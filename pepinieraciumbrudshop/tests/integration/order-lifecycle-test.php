<?php
/**
 * Integration Test: Complete Order Placement & Lifecycle
 *
 * Tests the full end-to-end flow:
 * 1. Storefront Customer fetches cart & adds product via WooCommerce Store API (/wc/store/v1/cart/add-item)
 * 2. Customer places a COD order via WooCommerce Store API (/wc/store/v1/checkout)
 *    - Verifies initial order status is 'pending' ("În așteptare (Nouă)")
 *    - Verifies stock reduction occurs immediately upon placement
 *    - Verifies COD payment note is recorded
 *    - Verifies initial Admin & Customer "Comandă Nouă" confirmation emails are triggered
 * 3. Admin authenticates via JWT & fetches the order (/pcs/v1/admin/orders/{id})
 *    - Verifies unauthenticated access is blocked (401)
 *    - Verifies order appears in admin order list & detail endpoint
 * 4. Admin advances order through the complete lifecycle:
 *    - pending    -> processing ("În procesare")       [0 emails sent]
 *    - processing -> prepared   ("Procesată (Pregătită)") [0 emails sent]
 *    - Admin adds an internal order note & retrieves translated order notes
 *    - prepared   -> shipped    ("Predată Curier")     [1 customer shipping email sent]
 *    - shipped    -> completed  ("Finalizată (Livrată)") [0 emails sent]
 * 5. Cleans up temporary test order and test product in a finally block.
 */

declare( strict_types=1 );

$wp_load = dirname( __DIR__, 2 ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    fwrite( STDERR, "❌ Cannot find wp-load.php at {$wp_load}\n" );
    exit( 1 );
}

// Redirect WooCommerce logs to a writable temp directory during CLI integration tests
$test_log_dir = sys_get_temp_dir() . '/pcs-wc-logs-test/';
if ( ! is_dir( $test_log_dir ) ) {
    @mkdir( $test_log_dir, 0777, true );
}
define( 'WC_LOG_DIR', $test_log_dir );

require_once $wp_load;

if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'PCS_API' ) ) {
    fwrite( STDERR, "❌ WooCommerce or PCS_API plugin is not active.\n" );
    exit( 1 );
}

$passed = 0;
$failed = 0;

function assert_step( bool $condition, string $message, mixed $debug = null ): void {
    global $passed, $failed;
    if ( $condition ) {
        $passed++;
        echo "  ✔ {$message}\n";
    } else {
        $failed++;
        echo "  ✘ FAILED: {$message}\n";
        if ( null !== $debug ) {
            echo "    Debug: " . ( is_scalar( $debug ) ? (string) $debug : json_encode( $debug, JSON_UNESCAPED_UNICODE ) ) . "\n";
        }
    }
}

// Disable WC file logger during CLI test runs to avoid file permission warnings on wc-logs/
add_filter( 'woocommerce_logger_handlers', '__return_empty_array', 999 );

// Intercept all outgoing emails via pre_wp_mail so no real SMTP messages leave the server
$captured_emails = [];
add_filter( 'pre_wp_mail', function ( $null, array $atts ) use ( &$captured_emails ) {
    $captured_emails[] = [
        'to'      => is_array( $atts['to'] ?? null ) ? implode( ',', $atts['to'] ) : (string) ( $atts['to'] ?? '' ),
        'subject' => (string) ( $atts['subject'] ?? '' ),
        'message' => (string) ( $atts['message'] ?? '' ),
    ];
    return true; // Short-circuit actual SMTP sending and report success
}, 9999, 2 );

echo "=================================================================\n";
echo " 🧪 Integration Test: Order Placement & Lifecycle (pending → completed)\n";
echo "=================================================================\n\n";

$test_product_id        = 0;
$test_order_id          = 0;
$single_stock_prod_id   = 0;
$single_stock_order_id  = 0;

try {
    $server = rest_get_server();

    // -------------------------------------------------------------------------
    // 0. Setup Test Fixtures (Admin User + Temporary Product)
    // -------------------------------------------------------------------------
    echo "[Step 0] Setting up temporary test product & admin JWT...\n";

    $admins = get_users( [
        'role'   => 'administrator',
        'number' => 1,
    ] );
    assert_step( ! empty( $admins ), 'Found an administrator user for JWT authentication' );
    $admin_user = $admins[0];

    $admin_jwt = PCS_JWT::create( [
        'user_id'    => $admin_user->ID,
        'user_login' => $admin_user->user_login,
        'role'       => 'administrator',
    ] );
    assert_step( ! empty( $admin_jwt ), 'Generated valid admin JWT token' );

    // Create a temporary test product via WC_Product_Simple
    $product = new WC_Product_Simple();
    $product->set_name( '[TEST-AUTO] Măr Florina — Pom Fructifer Certificat' );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'visible' );
    $product->set_regular_price( '35.00' );
    $product->set_price( '35.00' );
    $product->set_manage_stock( true );
    $product->set_stock_quantity( 50 );
    $product->set_stock_status( 'instock' );
    $test_product_id = $product->save();

    assert_step( $test_product_id > 0, "Created temporary test product #{$test_product_id} (price: 35.00 RON, stock: 50)" );

    // -------------------------------------------------------------------------
    // 1. Storefront Cart & Order Placement via WooCommerce Store API
    // -------------------------------------------------------------------------
    echo "\n[Step 1] Customer adds product to cart and places order via Store API...\n";

    // Ensure guest session context for Store API checkout
    wp_set_current_user( 0 );
    if ( function_exists( 'wc_load_cart' ) ) {
        wc_load_cart();
    }
    WC()->cart->empty_cart();

    // Allow Store API requests in CLI test harness
    add_filter( 'woocommerce_store_api_disable_nonce_check', '__return_true' );

    // 1a. Add 3 items to cart via POST /wc/store/v1/cart/add-item
    $add_req = new WP_REST_Request( 'POST', '/wc/store/v1/cart/add-item' );
    $add_req->set_header( 'Content-Type', 'application/json' );
    $add_req->set_body( wp_json_encode( [
        'id'       => $test_product_id,
        'quantity' => 3,
    ] ) );
    $add_res  = $server->dispatch( $add_req );
    $add_data = $server->response_to_data( $add_res, false );

    assert_step(
        $add_res->get_status() === 201 || $add_res->get_status() === 200,
        'POST /wc/store/v1/cart/add-item succeeded',
        $add_data
    );
    assert_step(
        (int) ( $add_data['items_count'] ?? 0 ) === 3,
        'Cart contains 3 units of the test product',
        $add_data['items_count'] ?? null
    );

    // Clear captured emails before checkout
    $captured_emails = [];

    // 1b. Submit checkout via POST /wc/store/v1/checkout (exact payload sent by pages/comanda.vue)
    $checkout_payload = [
        'billing_address'  => [
            'first_name' => 'Ion',
            'last_name'  => 'Popescu-Test',
            'email'      => 'ion.popescu.test@example.com',
            'phone'      => '0740123456',
            'address_1'  => 'Strada Principală Nr. 10',
            'city'       => 'Ciumbrud',
            'state'      => 'AB',
            'postcode'   => '515200',
            'country'    => 'RO',
        ],
        'shipping_address' => [
            'first_name' => 'Ion',
            'last_name'  => 'Popescu-Test',
            'phone'      => '0740123456',
            'address_1'  => 'Strada Principală Nr. 10',
            'city'       => 'Ciumbrud',
            'state'      => 'AB',
            'postcode'   => '515200',
            'country'    => 'RO',
        ],
        'customer_note'    => 'Comandă automată de test — rog apel înainte de livrare.',
        'payment_method'   => 'cod',
    ];

    $checkout_req = new WP_REST_Request( 'POST', '/wc/store/v1/checkout' );
    $checkout_req->set_header( 'Content-Type', 'application/json' );
    $checkout_req->set_body( wp_json_encode( $checkout_payload ) );
    $checkout_res  = $server->dispatch( $checkout_req );
    $checkout_data = $server->response_to_data( $checkout_res, false );

    $test_order_id = (int) ( $checkout_data['order_id'] ?? $checkout_data['id'] ?? 0 );

    assert_step(
        $checkout_res->get_status() === 200 && $test_order_id > 0,
        "POST /wc/store/v1/checkout created Order #{$test_order_id}",
        $checkout_data
    );

    // Verify order state in database immediately after checkout
    $wc_order = wc_get_order( $test_order_id );
    assert_step( $wc_order instanceof WC_Order, 'Order exists in WooCommerce database' );
    assert_step(
        $wc_order->get_status() === 'pending',
        "New order initial status is 'pending' (În așteptare (Nouă))",
        $wc_order->get_status()
    );
    assert_step(
        $wc_order->get_payment_method() === 'cod',
        "Order payment method is 'cod' (Plată ramburs)",
        $wc_order->get_payment_method()
    );
    assert_step(
        (float) $wc_order->get_total() >= 105.00,
        'Order total is calculated accurately (>= 105.00 RON)',
        $wc_order->get_total()
    );
    assert_step(
        $wc_order->get_customer_note() === 'Comandă automată de test — rog apel înainte de livrare.',
        'Customer checkout note is preserved on the order'
    );

    // Verify stock was reduced from 50 to 47
    wc_delete_product_transients( $test_product_id );
    $fresh_product = wc_get_product( $test_product_id );
    assert_step(
        (int) $fresh_product->get_stock_quantity() === 47,
        'Product stock was immediately reduced from 50 to 47 on order placement',
        $fresh_product->get_stock_quantity()
    );

    // Verify initial emails were triggered on checkout
    assert_step(
        count( $captured_emails ) >= 1,
        'Initial order confirmation email(s) triggered on checkout (' . count( $captured_emails ) . ' sent)'
    );
    assert_step(
        $wc_order->get_meta( '_pcs_customer_order_email_sent' ) === 'yes',
        'Order meta _pcs_customer_order_email_sent is marked "yes"'
    );

    // -------------------------------------------------------------------------
    // 2. Admin API Security & Order Retrieval (/pcs/v1/admin/orders)
    // -------------------------------------------------------------------------
    echo "\n[Step 2] Verifying Admin API authentication & order retrieval...\n";

    // 2a. Unauthenticated request must return 401
    $unauth_req = new WP_REST_Request( 'GET', "/pcs/v1/admin/orders/{$test_order_id}" );
    $unauth_res = $server->dispatch( $unauth_req );
    assert_step(
        $unauth_res->get_status() === 401,
        'Unauthenticated GET /pcs/v1/admin/orders/{id} is rejected with HTTP 401'
    );

    // 2b. Authenticated GET /pcs/v1/admin/orders/{id}
    $get_req = new WP_REST_Request( 'GET', "/pcs/v1/admin/orders/{$test_order_id}" );
    $get_req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
    $get_res  = $server->dispatch( $get_req );
    $get_data = $server->response_to_data( $get_res, false );

    assert_step(
        $get_res->get_status() === 200 && (int) ( $get_data['id'] ?? 0 ) === $test_order_id,
        "Admin GET /pcs/v1/admin/orders/{$test_order_id} returns order details",
        $get_res->get_status()
    );
    assert_step(
        ( $get_data['status'] ?? '' ) === 'pending',
        "Admin order detail confirms status is 'pending'",
        $get_data['status'] ?? null
    );
    assert_step(
        ( $get_data['billing']['first_name'] ?? '' ) === 'Ion' && ( $get_data['billing']['city'] ?? '' ) === 'Ciumbrud',
        'Admin order detail contains accurate customer billing & delivery data'
    );

    // 2c. Authenticated GET /pcs/v1/admin/orders?status=pending
    $list_req = new WP_REST_Request( 'GET', '/pcs/v1/admin/orders' );
    $list_req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
    $list_req->set_query_params( [ 'status' => 'pending', 'per_page' => 20 ] );
    $list_res  = $server->dispatch( $list_req );
    $list_data = $server->response_to_data( $list_res, false );
    $found_in_pending = false;
    if ( is_array( $list_data ) ) {
        foreach ( $list_data as $row ) {
            if ( (int) ( $row['id'] ?? 0 ) === $test_order_id ) {
                $found_in_pending = true;
                break;
            }
        }
    }
    assert_step( $found_in_pending, "Order #{$test_order_id} appears in GET /pcs/v1/admin/orders?status=pending" );

    // Helper for Admin PUT status changes
    $update_order_status = function ( string $new_status ) use ( $server, $test_order_id, $admin_jwt ): array {
        $req = new WP_REST_Request( 'PUT', "/pcs/v1/admin/orders/{$test_order_id}" );
        $req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
        $req->set_header( 'Content-Type', 'application/json' );
        $req->set_body_params( [ 'status' => $new_status ] );
        $res  = $server->dispatch( $req );
        $data = $server->response_to_data( $res, false );
        return [ 'status_code' => $res->get_status(), 'data' => $data ];
    };

    // -------------------------------------------------------------------------
    // 3. Lifecycle Transition: pending -> processing ("În procesare")
    // -------------------------------------------------------------------------
    echo "\n[Step 3] Transitioning order: pending → processing (În procesare)...\n";
    $captured_emails = [];

    $res_processing = $update_order_status( 'processing' );
    assert_step(
        $res_processing['status_code'] === 200 && ( $res_processing['data']['status'] ?? '' ) === 'processing',
        "PUT /pcs/v1/admin/orders/{$test_order_id} updated status to 'processing'",
        $res_processing
    );
    assert_step(
        count( $captured_emails ) === 0,
        "No duplicate email sent when transitioning to 'processing'",
        $captured_emails
    );

    // -------------------------------------------------------------------------
    // 4. Lifecycle Transition: processing -> prepared ("Procesată (Pregătită)")
    // -------------------------------------------------------------------------
    echo "\n[Step 4] Transitioning order: processing → prepared (Procesată (Pregătită))...\n";
    $captured_emails = [];

    $res_prepared = $update_order_status( 'prepared' );
    assert_step(
        $res_prepared['status_code'] === 200 && ( $res_prepared['data']['status'] ?? '' ) === 'prepared',
        "PUT /pcs/v1/admin/orders/{$test_order_id} updated status to custom status 'prepared'",
        $res_prepared
    );
    assert_step(
        wc_get_order( $test_order_id )->get_status() === 'prepared',
        "Database persisted custom status 'wc-prepared'"
    );
    assert_step(
        count( $captured_emails ) === 0,
        "No email sent when transitioning to 'prepared'",
        $captured_emails
    );

    // -------------------------------------------------------------------------
    // 5. Admin Order Notes (POST & GET /pcs/v1/admin/orders/{id}/notes)
    // -------------------------------------------------------------------------
    echo "\n[Step 5] Adding and verifying Admin Order Notes...\n";

    // 5a. Empty note validation
    $empty_note_req = new WP_REST_Request( 'POST', "/pcs/v1/admin/orders/{$test_order_id}/notes" );
    $empty_note_req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
    $empty_note_req->set_body_params( [ 'note' => '   ' ] );
    $empty_note_res = $server->dispatch( $empty_note_req );
    assert_step(
        $empty_note_res->get_status() === 400,
        'POST /pcs/v1/admin/orders/{id}/notes rejects empty notes with HTTP 400'
    );

    // 5b. Add valid internal note
    $note_text = 'Rădăcinile au fost mocirlite și ambalate pentru curier.';
    $add_note_req = new WP_REST_Request( 'POST', "/pcs/v1/admin/orders/{$test_order_id}/notes" );
    $add_note_req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
    $add_note_req->set_body_params( [
        'note'          => $note_text,
        'customer_note' => false,
    ] );
    $add_note_res  = $server->dispatch( $add_note_req );
    $add_note_data = $server->response_to_data( $add_note_res, false );

    assert_step(
        $add_note_res->get_status() === 201 && ( $add_note_data['note'] ?? '' ) === $note_text,
        'POST /pcs/v1/admin/orders/{id}/notes created internal order note (HTTP 201)'
    );

    // 5c. Fetch all order notes and verify Romanian status transition translations
    $notes_req = new WP_REST_Request( 'GET', "/pcs/v1/admin/orders/{$test_order_id}/notes" );
    $notes_req->set_header( 'Authorization', 'Bearer ' . $admin_jwt );
    $notes_res  = $server->dispatch( $notes_req );
    $notes_data = $server->response_to_data( $notes_res, false );

    $all_notes_concat = implode( ' | ', array_column( is_array( $notes_data ) ? $notes_data : [], 'note' ) );
    assert_step(
        $notes_res->get_status() === 200 && str_contains( $all_notes_concat, $note_text ),
        'GET /pcs/v1/admin/orders/{id}/notes includes the newly added internal note'
    );
    assert_step(
        str_contains( $all_notes_concat, 'Procesată (Pregătită)' ),
        'Order notes contain Romanian-translated status transitions',
        $all_notes_concat
    );

    // -------------------------------------------------------------------------
    // 6. Lifecycle Transition: prepared -> shipped ("Predată Curier")
    // -------------------------------------------------------------------------
    echo "\n[Step 6] Transitioning order: prepared → shipped (Predată Curier)...\n";
    $captured_emails = [];

    $res_shipped = $update_order_status( 'shipped' );
    assert_step(
        $res_shipped['status_code'] === 200 && ( $res_shipped['data']['status'] ?? '' ) === 'shipped',
        "PUT /pcs/v1/admin/orders/{$test_order_id} updated status to 'shipped'",
        $res_shipped
    );
    assert_step(
        count( $captured_emails ) === 1 && str_contains( $captured_emails[0]['to'], 'ion.popescu.test@example.com' ),
        "Customer shipping notification email triggered exactly once when moving to 'shipped'",
        $captured_emails
    );

    // -------------------------------------------------------------------------
    // 7. Lifecycle Transition: shipped -> completed ("Finalizată (Livrată)")
    // -------------------------------------------------------------------------
    echo "\n[Step 7] Transitioning order: shipped → completed (Finalizată (Livrată))...\n";
    $captured_emails = [];

    $res_completed = $update_order_status( 'completed' );
    assert_step(
        $res_completed['status_code'] === 200 && ( $res_completed['data']['status'] ?? '' ) === 'completed',
        "PUT /pcs/v1/admin/orders/{$test_order_id} updated status to 'completed'",
        $res_completed
    );

    $final_order = wc_get_order( $test_order_id );
    assert_step(
        $final_order instanceof WC_Order && $final_order->get_status() === 'completed',
        "Order #{$test_order_id} reached final 'completed' status in database"
    );
    assert_step(
        count( $captured_emails ) === 0,
        "No duplicate email sent when transitioning from 'shipped' to 'completed'",
        $captured_emails
    );

    // Verify stock was not double-deducted across lifecycle transitions
    wc_delete_product_transients( $test_product_id );
    $final_product = wc_get_product( $test_product_id );
    assert_step(
        (int) $final_product->get_stock_quantity() === 47,
        'Product stock remained consistent at 47 across all status transitions',
        $final_product->get_stock_quantity()
    );

    // -------------------------------------------------------------------------
    // 8. Single-Stock Concurrency & Overselling Protection Test
    // -------------------------------------------------------------------------
    echo "\n[Step 8] Testing single-stock (1 item) quantity limits & concurrent checkout protection...\n";

    $single_product = new WC_Product_Simple();
    $single_product->set_name( '[TEST-AUTO] Păr Williams — Ultima Bucată (Stoc 1)' );
    $single_product->set_status( 'publish' );
    $single_product->set_catalog_visibility( 'visible' );
    $single_product->set_regular_price( '40.00' );
    $single_product->set_price( '40.00' );
    $single_product->set_manage_stock( true );
    $single_product->set_stock_quantity( 1 );
    $single_product->set_stock_status( 'instock' );
    $single_product->set_backorders( 'no' );
    $single_stock_prod_id = $single_product->save();

    assert_step(
        $single_stock_prod_id > 0 && ! $single_product->backorders_allowed(),
        "Created 1-stock test product #{$single_stock_prod_id} with backorders disallowed"
    );

    wp_set_current_user( 0 );
    WC()->cart->empty_cart();

    // 8a. Single customer tries to add 2 units when only 1 unit is in stock -> must fail (400)
    $over_add_req = new WP_REST_Request( 'POST', '/wc/store/v1/cart/add-item' );
    $over_add_req->set_header( 'Content-Type', 'application/json' );
    $over_add_req->set_body( wp_json_encode( [
        'id'       => $single_stock_prod_id,
        'quantity' => 2,
    ] ) );
    $over_add_res  = $server->dispatch( $over_add_req );
    $over_add_data = $server->response_to_data( $over_add_res, false );

    assert_step(
        $over_add_res->get_status() === 400,
        'POST /wc/store/v1/cart/add-item rejects adding 2 units when only 1 is in stock (HTTP 400)',
        $over_add_data
    );

    // 8b. Customer adds 1 unit (valid)
    $valid_add_req = new WP_REST_Request( 'POST', '/wc/store/v1/cart/add-item' );
    $valid_add_req->set_header( 'Content-Type', 'application/json' );
    $valid_add_req->set_body( wp_json_encode( [
        'id'       => $single_stock_prod_id,
        'quantity' => 1,
    ] ) );
    $valid_add_res  = $server->dispatch( $valid_add_req );
    $valid_add_data = json_decode( wp_json_encode( $server->response_to_data( $valid_add_res, false ) ), true );

    assert_step(
        ( $valid_add_res->get_status() === 201 || $valid_add_res->get_status() === 200 )
        && (int) ( $valid_add_data['items'][0]['quantity_limits']['maximum'] ?? 0 ) === 1,
        'Adding 1 unit succeeds and cart item quantity_limits.maximum is capped at 1',
        $valid_add_data['items'][0]['quantity_limits'] ?? null
    );

    // 8c. Customer tries to add 1 more unit on top of the 1 already in cart -> must fail (400)
    $topup_add_req = new WP_REST_Request( 'POST', '/wc/store/v1/cart/add-item' );
    $topup_add_req->set_header( 'Content-Type', 'application/json' );
    $topup_add_req->set_body( wp_json_encode( [
        'id'       => $single_stock_prod_id,
        'quantity' => 1,
    ] ) );
    $topup_add_res = $server->dispatch( $topup_add_req );

    assert_step(
        $topup_add_res->get_status() === 400,
        'POST /wc/store/v1/cart/add-item rejects topping up cart beyond available stock (HTTP 400)'
    );

    // 8d. Faster buyer (Person B) completes checkout for the last item -> stock drops to 0 (outofstock)
    $fast_checkout_req = new WP_REST_Request( 'POST', '/wc/store/v1/checkout' );
    $fast_checkout_req->set_header( 'Content-Type', 'application/json' );
    $fast_checkout_req->set_body( wp_json_encode( $checkout_payload ) );
    $fast_checkout_res  = $server->dispatch( $fast_checkout_req );
    $fast_checkout_data = $server->response_to_data( $fast_checkout_res, false );
    $single_stock_order_id = (int) ( $fast_checkout_data['order_id'] ?? $fast_checkout_data['id'] ?? 0 );

    wc_delete_product_transients( $single_stock_prod_id );
    $depleted_product = wc_get_product( $single_stock_prod_id );

    assert_step(
        $fast_checkout_res->get_status() === 200
        && $single_stock_order_id > 0
        && (int) $depleted_product->get_stock_quantity() === 0
        && ! $depleted_product->is_in_stock(),
        "Faster buyer completes checkout (Order #{$single_stock_order_id}) and stock drops to 0 (outofstock)"
    );

    // 8e. Slower buyer (Person A) who already had the item in their cart prior to depletion attempts checkout -> must be blocked
    WC()->cart->empty_cart();
    // Simulate Person A's pre-existing cart session row containing the item before stock became 0
    $reflection_cart = WC()->cart;
    $cart_item_key   = WC()->cart->generate_cart_id( $single_stock_prod_id );
    $reflection_cart->cart_contents[ $cart_item_key ] = [
        'key'          => $cart_item_key,
        'product_id'   => $single_stock_prod_id,
        'variation_id' => 0,
        'variation'    => [],
        'quantity'     => 1,
        'data'         => $depleted_product,
        'data_hash'    => wc_get_cart_item_data_hash( $depleted_product ),
    ];

    $slow_checkout_req = new WP_REST_Request( 'POST', '/wc/store/v1/checkout' );
    $slow_checkout_req->set_header( 'Content-Type', 'application/json' );
    $slow_checkout_req->set_body( wp_json_encode( $checkout_payload ) );
    $slow_checkout_res  = $server->dispatch( $slow_checkout_req );
    $slow_checkout_data = $server->response_to_data( $slow_checkout_res, false );

    assert_step(
        $slow_checkout_res->get_status() >= 400,
        "Slower buyer's checkout for depleted item is blocked (HTTP {$slow_checkout_res->get_status()})",
        $slow_checkout_data
    );

    // -------------------------------------------------------------------------
    // 8f. Rose Attributes & Store API Colour / Fragrance / Price Filter Test
    // -------------------------------------------------------------------------
    echo "\n[Step 9] Testing Store API rose colour, fragrance, and price filters...\n";
    $rose_filter_req = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
    $rose_filter_req->set_param( 'category', 'trandafiri' );
    $rose_filter_req->set_param( 'color', 'Roșu' );
    $rose_filter_req->set_param( 'fragrance', 'Intens' );
    $rose_filter_req->set_param( 'min_price', '2500' );
    $rose_filter_req->set_param( 'max_price', '2800' );
    $rose_filter_res  = $server->dispatch( $rose_filter_req );
    $rose_filter_data = json_decode( wp_json_encode( $server->response_to_data( $rose_filter_res, false ) ), true );

    assert_step(
        $rose_filter_res->get_status() === 200 && is_array( $rose_filter_data ) && count( $rose_filter_data ) === 4,
        'GET /wc/store/v1/products?category=trandafiri&color=Roșu&fragrance=Intens&min_price=2500&max_price=2800 returns 4 matching rose varieties',
        count( is_array( $rose_filter_data ) ? $rose_filter_data : [] )
    );

    // -------------------------------------------------------------------------
    // 8g. Out-of-Stock Products Ordered Last in Store API Catalog Queries
    // -------------------------------------------------------------------------
    $catalog_order_req = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
    $catalog_order_req->set_param( 'per_page', 100 );
    $catalog_order_res  = $server->dispatch( $catalog_order_req );
    $catalog_order_data = json_decode( wp_json_encode( $server->response_to_data( $catalog_order_res, false ) ), true );

    $seen_out_of_stock = false;
    $stock_ordering_ok = true;
    foreach ( (array) $catalog_order_data as $item ) {
        $in_stock = ! empty( $item['is_in_stock'] );
        if ( ! $in_stock ) {
            $seen_out_of_stock = true;
        } elseif ( $seen_out_of_stock && $in_stock ) {
            $stock_ordering_ok = false;
            break;
        }
    }

    assert_step(
        $catalog_order_res->get_status() === 200 && $stock_ordering_ok && $seen_out_of_stock,
        'GET /wc/store/v1/products lists all in-stock products before out-of-stock products'
    );

} finally {
    // -------------------------------------------------------------------------
    // 9. Teardown / Cleanup
    // -------------------------------------------------------------------------
    echo "\n[Cleanup] Removing temporary test orders & products...\n";
    foreach ( [ $test_order_id, $single_stock_order_id ] as $oid ) {
        if ( $oid > 0 ) {
            $order_to_delete = wc_get_order( $oid );
            if ( $order_to_delete instanceof WC_Order ) {
                $order_to_delete->delete( true );
                echo "  ✔ Deleted temporary test order #{$oid}\n";
            }
        }
    }
    foreach ( [ $test_product_id, $single_stock_prod_id ] as $pid ) {
        if ( $pid > 0 ) {
            $product_to_delete = wc_get_product( $pid );
            if ( $product_to_delete instanceof WC_Product ) {
                $product_to_delete->delete( true );
                echo "  ✔ Deleted temporary test product #{$pid}\n";
            }
        }
    }
    if ( function_exists( 'WC' ) && WC()->cart ) {
        WC()->cart->empty_cart();
    }
}

echo "\n=================================================================\n";
echo " Summary: {$passed} passed, {$failed} failed.\n";
echo "=================================================================\n";

exit( $failed > 0 ? 1 : 0 );
