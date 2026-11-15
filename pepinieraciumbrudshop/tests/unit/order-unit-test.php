<?php
/**
 * Standalone Unit Tests for Pepiniera Ciumbrud V2 Backend (JWT & Order Lifecycle Note Formatting).
 * Can run in any environment (including CI without a live database).
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}
if ( ! defined( 'PCS_API_NAMESPACE' ) ) {
    define( 'PCS_API_NAMESPACE', 'pcs/v1' );
}
if ( ! defined( 'PCS_JWT_SECRET' ) ) {
    define( 'PCS_JWT_SECRET', 'unit-test-secret-key-pepiniera-ciumbrud-2026' );
}

// Minimal WordPress stubs when running outside full WP bootstrap
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public function __construct(
            private string $code = '',
            private string $message = '',
            private mixed $data = ''
        ) {}
        public function get_error_code(): string { return $this->code; }
        public function get_error_message(): string { return $this->message; }
        public function get_error_data(): mixed { return $this->data; }
    }
}
if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( mixed $thing ): bool {
        return $thing instanceof WP_Error;
    }
}
if ( ! class_exists( 'WP_REST_Request' ) ) {
    class WP_REST_Request {}
}
if ( ! class_exists( 'WP_REST_Response' ) ) {
    class WP_REST_Response {
        public function __construct( public mixed $data = null, public int $status = 200 ) {}
    }
}
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( mixed $data ): string|false {
        return json_encode( $data );
    }
}
if ( ! function_exists( 'home_url' ) ) {
    function home_url( string $path = '' ): string {
        return 'http://localhost' . $path;
    }
}
if ( ! function_exists( 'wp_salt' ) ) {
    function wp_salt( string $scheme = 'auth' ): string {
        return 'unit-test-salt-' . $scheme;
    }
}

$plugin_dir = dirname( __DIR__, 2 ) . '/wp-content/plugins/pepinieraciumbrud-api';
require_once $plugin_dir . '/includes/class-pcs-jwt.php';
require_once $plugin_dir . '/includes/class-pcs-wc-proxy.php';
require_once $plugin_dir . '/includes/class-pcs-base-routes.php';
require_once $plugin_dir . '/includes/class-pcs-orders-routes.php';

$passed = 0;
$failed = 0;

function assert_true( bool $condition, string $label ): void {
    global $passed, $failed;
    if ( $condition ) {
        $passed++;
        echo "  ✔ {$label}\n";
    } else {
        $failed++;
        echo "  ✘ FAILED: {$label}\n";
    }
}

function assert_same( mixed $expected, mixed $actual, string $label ): void {
    global $passed, $failed;
    if ( $expected === $actual ) {
        $passed++;
        echo "  ✔ {$label}\n";
    } else {
        $failed++;
        echo "  ✘ FAILED: {$label}\n    Expected: " . var_export( $expected, true ) . "\n    Actual:   " . var_export( $actual, true ) . "\n";
    }
}

echo "🧪 Running Backend Unit Tests (JWT & Order Lifecycle Utilities)...\n";

// 1. JWT Authentication Tests
$token = PCS_JWT::create( [
    'user_id'    => 1,
    'user_login' => 'admin',
    'role'       => 'administrator',
], 3600 );

$decoded = PCS_JWT::validate( $token );
assert_true( is_array( $decoded ), 'JWT: Valid token decodes to payload array' );
assert_same( 1, $decoded['user_id'] ?? null, 'JWT: Preserves user_id claim' );
assert_same( 'administrator', $decoded['role'] ?? null, 'JWT: Preserves role claim' );

$tampered = $token . 'tampered';
$tampered_res = PCS_JWT::validate( $tampered );
assert_true( is_wp_error( $tampered_res ) && $tampered_res->get_error_code() === 'pcs_jwt_invalid_sig', 'JWT: Rejects tampered signature' );

$malformed_res = PCS_JWT::validate( 'not-a-valid-jwt' );
assert_true( is_wp_error( $malformed_res ) && $malformed_res->get_error_code() === 'pcs_jwt_malformed', 'JWT: Rejects malformed token' );

$expired_token = PCS_JWT::create( [ 'user_id' => 1 ], -10 );
$expired_res = PCS_JWT::validate( $expired_token );
assert_true( is_wp_error( $expired_res ) && $expired_res->get_error_code() === 'pcs_jwt_expired', 'JWT: Rejects expired token' );

// 2. Order Lifecycle Status Transition Note Translations
assert_same(
    "Starea comenzii a fost modificată din 'În așteptare (Nouă)' în 'În procesare'.",
    PCS_Orders_Routes::translate_order_note_text( 'Order status changed from Pending to Processing.' ),
    'Order Note: Translates Pending -> Processing'
);

assert_same(
    "Starea comenzii a fost modificată din 'În așteptarea plății' în 'În procesare'.",
    PCS_Orders_Routes::translate_order_note_text( 'Order status changed from Pending payment to Processing.' ),
    'Order Note: Translates Pending payment -> Processing'
);

assert_same(
    "Starea comenzii a fost modificată din 'În procesare' în 'Procesată (Pregătită)'.",
    PCS_Orders_Routes::translate_order_note_text( 'Order status changed from Processing to Prepared.' ),
    'Order Note: Translates Processing -> Prepared'
);

assert_same(
    "Starea comenzii a fost modificată din 'Procesată (Pregătită)' în 'Predată Curier'.",
    PCS_Orders_Routes::translate_order_note_text( 'Order status changed from Prepared to Shipped.' ),
    'Order Note: Translates Prepared -> Shipped'
);

assert_same(
    "Starea comenzii a fost modificată din 'Predată Curier' în 'Finalizată (Livrată)'.",
    PCS_Orders_Routes::translate_order_note_text( 'Order status changed from Shipped to Completed.' ),
    'Order Note: Translates Shipped -> Completed'
);

assert_same(
    'Nivel stoc redus: Măr Ionatan (#42) 50→47',
    PCS_Orders_Routes::translate_order_note_text( 'Stock levels reduced: Măr Ionatan (#42) 50→47' ),
    'Order Note: Translates stock level reduction'
);

assert_same(
    'Plată ramburs la livrare. Starea comenzii a fost modificată din \'În așteptarea plății\' în \'În procesare\'.',
    PCS_Orders_Routes::translate_order_note_text( 'Payment to be made upon delivery. Order status changed from Pending payment to Processing.' ),
    'Order Note: Translates COD payment + status transition'
);

assert_same(
    'Emailul «Comandă Nouă» a fost trimis.',
    PCS_Orders_Routes::translate_order_note_text( 'Email "Processing order" sent.' ),
    'Order Note: Translates customer confirmation email note to Comandă Nouă'
);

assert_same(
    'Emailul «Comandă finalizată» a fost trimis.',
    PCS_Orders_Routes::translate_order_note_text( 'Email "Completed order" sent.' ),
    'Order Note: Translates shipped/completed email note'
);

echo "\nSummary: {$passed} passed, {$failed} failed.\n";
exit( $failed > 0 ? 1 : 0 );
