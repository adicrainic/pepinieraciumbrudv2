<?php
/**
 * Plugin Name: Pepinieraciumbrud API
 * Plugin URI:  https://www.pepinieraciumbrud.ro/
 * Description: Secure server-side API proxy layer for the Pepiniera Ciumbrud headless e-commerce platform. Bridges the Vue 3 Admin dashboard with WooCommerce REST API without exposing credentials to the browser. Provides JWT-authenticated admin endpoints and CORS configuration.
 * Version:     1.0.0
 * Author:      Pepiniera Ciumbrud
 * License:     GPL-2.0+
 * Text Domain: pepinieraciumbrud-api
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

define( 'PCS_API_VERSION', '1.0.0' );
define( 'PCS_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PCS_API_NAMESPACE', 'pcs/v1' );

// Load modules
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-jwt.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-wc-proxy.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-base-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-auth-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-products-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-categories-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-orders-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-customers-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-dashboard-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-media-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-settings-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-contact-routes.php';
require_once PCS_API_PLUGIN_DIR . 'includes/class-pcs-mailer.php';

/**
 * Main plugin class.
 */
final class PCS_API {

    private static ?self $instance = null;

    /**
     * Guard flag allowing WC_Email_New_Order (admin notification) only during initial checkout confirmation.
     */
    private bool $allowing_admin_new_order_email = false;

    /**
     * Guard flag allowing WC_Email_Customer_Processing_Order only during initial checkout confirmation.
     */
    private bool $allowing_customer_new_order_email = false;

    /**
     * Guard flag allowing WC_Email_Customer_Completed_Order only when order status transitions to 'shipped' (Predată Curier).
     */
    private bool $allowing_customer_shipped_email = false;

    /**
     * Guard flag allowing WC_Email_Customer_Cancelled_Order only when order status transitions to 'cancelled' (Anulată).
     */
    private bool $allowing_customer_cancelled_email = false;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize SMTP Mailer
        PCS_Mailer::instance();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'init', [ $this, 'handle_cors' ], 1 );
        add_filter( 'rest_allowed_cors_headers', [ $this, 'filter_allowed_cors_headers' ], 99 );
        add_filter( 'rest_exposed_cors_headers', [ $this, 'filter_exposed_cors_headers' ], 99 );
        add_filter( 'rest_pre_serve_request', [ $this, 'cors_pre_serve' ], 99 );
        add_filter( 'rest_post_dispatch', [ $this, 'filter_rest_post_dispatch_headers' ], 99, 3 );

        // Allow bulk / wholesale purchasing (quantities > 500)
        add_filter( 'woocommerce_store_api_product_quantity_maximum', fn() => 99999 );
        add_filter( 'woocommerce_store_api_product_quantity_limit', fn() => 99999 );
        add_filter( 'woocommerce_product_backorders_allowed', '__return_true' );

        // New orders status & lifecycle:
        // Register custom order statuses: 'wc-prepared' (Procesată (Pregătită)) and 'wc-shipped' (Predată Curier)
        add_action( 'init', [ $this, 'register_custom_order_statuses' ], 10 );
        add_filter( 'wc_order_statuses', [ $this, 'add_custom_order_statuses' ], 10, 1 );
        add_filter( 'woocommerce_reports_order_statuses', [ $this, 'include_custom_order_statuses_in_reports' ], 10, 1 );
        add_filter( 'woocommerce_order_is_paid_statuses', [ $this, 'include_custom_paid_statuses' ], 10, 1 );

        // Set new orders default and COD status to 'pending' (În așteptare (Nouă))
        add_filter( 'woocommerce_cod_process_payment_order_status', [ $this, 'filter_new_order_status' ], 10, 2 );
        add_filter( 'woocommerce_default_order_status', [ $this, 'filter_default_order_status' ] );

        // Prevent automatic cancellation of unpaid orders after hold_stock_minutes
        add_filter( 'woocommerce_cancel_unpaid_order', '__return_false' );

        // Stock management for pending and custom statuses
        remove_action( 'woocommerce_order_status_pending', 'wc_maybe_increase_stock_levels' );
        add_action( 'woocommerce_order_status_pending', 'wc_maybe_reduce_stock_levels' );
        add_action( 'woocommerce_order_status_prepared', 'wc_maybe_reduce_stock_levels' );
        add_action( 'woocommerce_order_status_shipped', 'wc_maybe_reduce_stock_levels' );

        // Handle checkout order processing (Store API & classic checkout)
        add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'on_checkout_order_processed' ], 20, 1 );
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'on_checkout_order_processed' ], 20, 1 );

        // Handle all order status transitions uniformly (from any status to any status):
        // - Moving to 'shipped' (Predată Curier) sends "Comanda ta de la Pepiniera Ciumbrud este pe drum!" once
        // - Moving to 'cancelled' (Anulată) sends "Comanda ta nr. #... a fost anulată" once
        // - Moving to any other status ('pending', 'processing', 'prepared', 'completed') sends NO email
        add_action( 'woocommerce_order_status_changed', [ $this, 'on_order_status_changed' ], 20, 4 );

        // Control email triggers strictly via guard flags so WooCommerce core's built-in
        // status transition hooks never fire unexpected or duplicate emails:
        // - 'new_order' (Admin) & 'customer_processing_order' (Customer) are ONLY sent on initial checkout ("Comandă Nouă")
        // - 'customer_completed_order' ("...este pe drum!") is ONLY sent when moving to 'shipped' (Predată Curier), never on 'completed'
        // - 'customer_cancelled_order' ("...a fost anulată") is ONLY sent once when moving to 'cancelled' (Anulată) from any status
        // - 'cancelled_order' (Admin) & 'customer_on_hold_order' are disabled for order status transitions to avoid duplicate notifications
        add_filter( 'woocommerce_email_enabled_new_order', [ $this, 'filter_new_order_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_enabled_customer_processing_order', [ $this, 'filter_customer_processing_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_enabled_customer_completed_order', [ $this, 'filter_customer_completed_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_enabled_customer_cancelled_order', [ $this, 'filter_customer_cancelled_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_enabled_cancelled_order', [ $this, 'filter_admin_cancelled_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_enabled_customer_on_hold_order', [ $this, 'filter_customer_on_hold_email_enabled' ], 10, 2 );
        add_filter( 'woocommerce_email_title', [ $this, 'filter_email_title' ], 10, 2 );

        // Enforce Pepiniera Ciumbrud Red & Warm Cream theme colors for WooCommerce emails
        add_filter( 'option_woocommerce_email_base_color', fn() => '#B21111' );
        add_filter( 'option_woocommerce_email_background_color', fn() => '#FAF6EE' );
        add_filter( 'option_woocommerce_email_body_background_color', fn() => '#FFFFFF' );
        add_filter( 'option_woocommerce_email_text_color', fn() => '#2E2C2A' );
        add_filter( 'option_woocommerce_email_footer_text_color', fn() => '#5F5A54' );

        // Email template overrides & customizations (Logo, "Produs" capitalization, no product images, detailed addresses with RO captions)
        add_filter( 'woocommerce_locate_template', [ $this, 'locate_email_template' ], 20, 3 );
        add_filter( 'woocommerce_locate_core_template', [ $this, 'locate_core_email_template' ], 20, 3 );
        add_filter( 'woocommerce_email_order_items_args', [ $this, 'filter_email_order_items_args' ], 20, 1 );
        add_filter( 'woocommerce_email_format_string', [ $this, 'filter_email_format_string' ], 20, 2 );
        add_filter( 'gettext', [ $this, 'filter_woocommerce_gettext' ], 20, 3 );
    }

    public function register_routes(): void {
        ( new PCS_Auth_Routes() )->register();
        ( new PCS_Products_Routes() )->register();
        ( new PCS_Categories_Routes() )->register();
        ( new PCS_Orders_Routes() )->register();
        ( new PCS_Customers_Routes() )->register();
        ( new PCS_Dashboard_Routes() )->register();
        ( new PCS_Media_Routes() )->register();
        ( new PCS_Settings_Routes() )->register();
        ( new PCS_Contact_Routes() )->register();
    }

    /**
     * Check if the given origin is allowed for CORS.
     */
    private function is_allowed_origin( string $origin ): bool {
        if ( empty( $origin ) ) {
            return false;
        }

        $allowed_explicit = [
            'http://localhost',
            'https://localhost',
            'http://localhost:3000',
            'http://localhost:5173',
            'http://127.0.0.1',
            'https://127.0.0.1',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:5173',
            'http://pepinieraciumbrud.local',
            'https://pepinieraciumbrud.local',
            'http://admin.pepinieraciumbrud.local',
            'https://admin.pepinieraciumbrud.local',
            'http://api.pepinieraciumbrud.local',
            'https://api.pepinieraciumbrud.local',
            defined( 'PCS_STOREFRONT_ORIGIN' ) ? PCS_STOREFRONT_ORIGIN : '',
            defined( 'PCS_ADMIN_ORIGIN' ) ? PCS_ADMIN_ORIGIN : '',
        ];
        $allowed_explicit = array_values( array_filter( array_unique( $allowed_explicit ) ) );

        if ( in_array( $origin, $allowed_explicit, true ) ) {
            return true;
        }

        $parsed = parse_url( $origin );
        $host   = $parsed['host'] ?? '';

        if ( $host === 'pepinieraciumbrud.local' || ( function_exists( 'str_ends_with' ) ? str_ends_with( $host, '.pepinieraciumbrud.local' ) : ( substr( $host, -strlen( '.pepinieraciumbrud.local' ) ) === '.pepinieraciumbrud.local' ) ) ) {
            return true;
        }
        if ( $host === 'localhost' || $host === '127.0.0.1' ) {
            return true;
        }

        return false;
    }

    /**
     * Build the string of allowed headers for CORS preflight.
     */
    private function get_cors_allow_headers(): string {
        $defaults = [
            'Authorization',
            'Content-Type',
            'X-WP-Nonce',
            'X-PCS-Token',
            'Nonce',
            'Cart-Token',
            'X-WC-Store-API-Nonce',
            'Accept',
            'Origin',
            'X-Requested-With',
            'Content-Disposition',
            'Content-MD5',
        ];

        if ( ! empty( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) ) {
            $requested = array_map( 'trim', explode( ',', $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) );
            $merged    = array_unique( array_merge( $defaults, $requested ) );
            return implode( ', ', $merged );
        }

        return implode( ', ', $defaults );
    }

    /**
     * Build the string of exposed headers for CORS.
     */
    private function get_cors_expose_headers(): string {
        $expose = [
            'Content-Type',
            'Authorization',
            'X-WP-Nonce',
            'X-PCS-Token',
            'Nonce',
            'Cart-Token',
            'X-WC-Store-API-Nonce',
            'X-WP-Total',
            'X-WP-TotalPages',
            'Link',
        ];
        return implode( ', ', $expose );
    }

    /**
     * Add CORS headers for storefront and admin origins.
     */
    public function handle_cors(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ( ! $origin || ! $this->is_allowed_origin( $origin ) ) {
            return;
        }

        header( 'Access-Control-Allow-Origin: ' . $origin );
        header( 'Access-Control-Allow-Credentials: true' );
        header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
        header( 'Access-Control-Allow-Headers: ' . $this->get_cors_allow_headers() );
        header( 'Access-Control-Expose-Headers: ' . $this->get_cors_expose_headers() );
        header( 'Access-Control-Max-Age: 86400' );
        header( 'Vary: Origin' );

        if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
            status_header( 204 );
            exit;
        }
    }

    /**
     * Filter the allowed headers for WordPress REST API CORS.
     */
    public function filter_allowed_cors_headers( array $headers ): array {
        return array_values( array_unique( array_merge( $headers, [
            'Authorization',
            'Content-Type',
            'X-WP-Nonce',
            'X-PCS-Token',
            'Nonce',
            'Cart-Token',
            'X-WC-Store-API-Nonce',
            'Accept',
            'Origin',
            'X-Requested-With',
            'Content-Disposition',
            'Content-MD5',
            'Cache-Control',
            'Pragma',
        ] ) ) );
    }

    /**
     * Filter the exposed headers for WordPress REST API CORS.
     */
    public function filter_exposed_cors_headers( array $headers ): array {
        return array_values( array_unique( array_merge( $headers, [
            'Content-Type',
            'Authorization',
            'X-WP-Nonce',
            'X-PCS-Token',
            'Nonce',
            'Cart-Token',
            'X-WC-Store-API-Nonce',
            'X-WP-Total',
            'X-WP-TotalPages',
            'Link',
        ] ) ) );
    }

    /**
     * Ensure CORS and cache prevention headers are also present on pre-served REST responses.
     */
    public function cors_pre_serve( bool $served ): bool {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ( $origin && $this->is_allowed_origin( $origin ) ) {
            header( 'Access-Control-Allow-Origin: ' . $origin );
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Expose-Headers: ' . $this->get_cors_expose_headers() );
            header( 'Vary: Origin' );
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if ( str_contains( $uri, '/wc/store' ) || str_contains( $uri, '/pcs/' ) ) {
            header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );
        }
        return $served;
    }

    /**
     * Prevent browser heuristic caching on WooCommerce Store API and PCS API responses.
     */
    public function filter_rest_post_dispatch_headers( $response, $server, $request ) {
        if ( $response instanceof WP_HTTP_Response || $response instanceof WP_REST_Response ) {
            $route = $request->get_route();
            if ( str_starts_with( $route, '/wc/store' ) || str_starts_with( $route, '/pcs/' ) ) {
                $response->header( 'Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0' );
                $response->header( 'Pragma', 'no-cache' );
                $response->header( 'Expires', '0' );
            }
        }
        return $response;
    }

    /**
     * Register custom WordPress post statuses for WooCommerce orders:
     * - wc-prepared: Procesată (Pregătită)
     * - wc-shipped:  Predată Curier
     */
    public function register_custom_order_statuses(): void {
        register_post_status( 'wc-prepared', [
            'label'                     => 'Procesată (Pregătită)',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            /* translators: %s: number of orders */
            'label_count'               => _n_noop(
                'Procesată (Pregătită) <span class="count">(%s)</span>',
                'Procesate (Pregătite) <span class="count">(%s)</span>',
                'pepinieraciumbrud-api'
            ),
        ] );

        register_post_status( 'wc-shipped', [
            'label'                     => 'Predată Curier',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            /* translators: %s: number of orders */
            'label_count'               => _n_noop(
                'Predată Curier <span class="count">(%s)</span>',
                'Predate Curier <span class="count">(%s)</span>',
                'pepinieraciumbrud-api'
            ),
        ] );
    }

    /**
     * Add custom order statuses to WooCommerce order statuses list in the exact workflow order.
     */
    public function add_custom_order_statuses( array $order_statuses ): array {
        $new_statuses = [];

        foreach ( $order_statuses as $key => $label ) {
            if ( 'wc-pending' === $key ) {
                $new_statuses[ $key ] = 'În așteptare (Nouă)';
                continue;
            }
            if ( 'wc-processing' === $key ) {
                $new_statuses['wc-processing'] = 'În procesare';
                $new_statuses['wc-prepared']   = 'Procesată (Pregătită)';
                $new_statuses['wc-shipped']    = 'Predată Curier';
                continue;
            }
            if ( 'wc-completed' === $key ) {
                $new_statuses[ $key ] = 'Finalizată (Livrată)';
                continue;
            }
            if ( 'wc-cancelled' === $key ) {
                $new_statuses[ $key ] = 'Anulată';
                continue;
            }
            $new_statuses[ $key ] = $label;
        }

        if ( ! isset( $new_statuses['wc-prepared'] ) ) {
            $new_statuses['wc-prepared'] = 'Procesată (Pregătită)';
        }
        if ( ! isset( $new_statuses['wc-shipped'] ) ) {
            $new_statuses['wc-shipped'] = 'Predată Curier';
        }

        return $new_statuses;
    }

    /**
     * Include custom active order statuses in WooCommerce reports.
     */
    public function include_custom_order_statuses_in_reports( mixed $statuses ): mixed {
        if ( is_array( $statuses ) ) {
            $statuses[] = 'prepared';
            $statuses[] = 'shipped';
            return array_values( array_unique( $statuses ) );
        }
        return $statuses;
    }

    /**
     * Include custom active order statuses in paid/valid order statuses.
     */
    public function include_custom_paid_statuses( array $statuses ): array {
        $statuses[] = 'prepared';
        $statuses[] = 'shipped';
        return array_values( array_unique( $statuses ) );
    }

    /**
     * Filter new order status for COD payments to 'pending' (În așteptare (Nouă)).
     */
    public function filter_new_order_status( string $status, ?WC_Order $order = null ): string {
        return 'pending';
    }

    /**
     * Filter default order status to 'pending' (În așteptare (Nouă)).
     */
    public function filter_default_order_status( string $status ): string {
        return 'pending';
    }

    /**
     * Ensure the email title logged in order notes for the initial customer confirmation email
     * is "Comandă Nouă" instead of "Comandă în procesare" to avoid confusion for the admin.
     */
    public function filter_email_title( string $title, mixed $email = null ): string {
        if ( is_object( $email ) && isset( $email->id ) ) {
            if ( in_array( $email->id, [ 'customer_processing_order', 'new_order' ], true ) ) {
                return 'Comandă Nouă';
            }
        }
        return $title;
    }

    /**
     * Handle checkout order processing for new orders:
     * - Reduce stock immediately
     * - Add note for COD payment
     * - Trigger admin "New order" email notification
     * - Trigger customer "Comandă Nouă" confirmation email notification
     */
    public function on_checkout_order_processed( mixed $order ): void {
        if ( is_numeric( $order ) ) {
            $order = wc_get_order( (int) $order );
        }
        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $order_id = $order->get_id();

        // 1. Ensure stock is reduced for the new order
        wc_maybe_reduce_stock_levels( $order_id );

        // 2. Add COD note if payment method is COD
        if ( 'cod' === $order->get_payment_method() ) {
            $order->add_order_note( __( 'Plată la livrare (Ramburs). Comandă primită în așteptare.', 'pepinieraciumbrud-api' ) );
        }

        // 3. Trigger Admin New Order notification email
        $mailer = WC()->mailer();
        $emails = $mailer ? $mailer->get_emails() : [];
        if ( isset( $emails['WC_Email_New_Order'] ) ) {
            if ( ! $order->get_new_order_email_sent() ) {
                $this->allowing_admin_new_order_email = true;
                try {
                    $emails['WC_Email_New_Order']->title = 'Comandă Nouă';
                    if ( $emails['WC_Email_New_Order']->is_enabled() ) {
                        $emails['WC_Email_New_Order']->trigger( $order_id, $order );
                    }
                } finally {
                    $this->allowing_admin_new_order_email = false;
                }
            }
        }

        // 4. Trigger Customer Order Received / Confirmation email (logged as "Emailul «Comandă Nouă» a fost trimis.")
        if ( isset( $emails['WC_Email_Customer_Processing_Order'] ) ) {
            if ( ! $order->get_meta( '_pcs_customer_order_email_sent' ) ) {
                $this->allowing_customer_new_order_email = true;
                try {
                    $emails['WC_Email_Customer_Processing_Order']->title = 'Comandă Nouă';
                    if ( $emails['WC_Email_Customer_Processing_Order']->is_enabled() ) {
                        $emails['WC_Email_Customer_Processing_Order']->trigger( $order_id, $order );
                        $order->update_meta_data( '_pcs_customer_order_email_sent', 'yes' );
                        $order->save();
                    }
                } finally {
                    $this->allowing_customer_new_order_email = false;
                }
            }
        }
    }

    /**
     * Central handler for order status changes (from any status to any status).
     * Only sends emails when the new status is 'shipped' (Predată Curier) or 'cancelled' (Anulată).
     */
    public function on_order_status_changed( int $order_id, string $from_status, string $to_status, ?WC_Order $order = null ): void {
        if ( $from_status === $to_status ) {
            return;
        }

        if ( 'shipped' === $to_status ) {
            $this->on_order_status_shipped( $order_id, $order );
        } elseif ( 'cancelled' === $to_status ) {
            $this->on_order_status_cancelled( $order_id, $order );
        }
    }

    /**
     * Send "Comanda ta de la Pepiniera Ciumbrud este pe drum!" email when order status
     * changes to 'shipped' (Predată Curier) from any status.
     */
    public function on_order_status_shipped( int $order_id, ?WC_Order $order = null ): void {
        if ( ! $order instanceof WC_Order ) {
            $order = wc_get_order( $order_id );
        }
        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $mailer = WC()->mailer();
        $emails = $mailer ? $mailer->get_emails() : [];

        if ( isset( $emails['WC_Email_Customer_Completed_Order'] ) ) {
            $this->allowing_customer_shipped_email = true;
            try {
                if ( $emails['WC_Email_Customer_Completed_Order']->is_enabled() ) {
                    $emails['WC_Email_Customer_Completed_Order']->trigger( $order_id, $order );
                }
            } finally {
                $this->allowing_customer_shipped_email = false;
            }
        }
    }

    /**
     * Send "Comanda ta nr. #... a fost anulată" email to the customer (exactly once)
     * when order status changes to 'cancelled' (Anulată) from any status.
     */
    public function on_order_status_cancelled( int $order_id, ?WC_Order $order = null ): void {
        if ( ! $order instanceof WC_Order ) {
            $order = wc_get_order( $order_id );
        }
        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $mailer = WC()->mailer();
        $emails = $mailer ? $mailer->get_emails() : [];

        if ( isset( $emails['WC_Email_Customer_Cancelled_Order'] ) ) {
            $this->allowing_customer_cancelled_email = true;
            try {
                if ( $emails['WC_Email_Customer_Cancelled_Order']->is_enabled() ) {
                    $emails['WC_Email_Customer_Cancelled_Order']->trigger( $order_id, $order );
                }
            } finally {
                $this->allowing_customer_cancelled_email = false;
            }
        }
    }

    /**
     * Only allow WC_Email_New_Order (admin notification) during initial checkout confirmation,
     * never on any subsequent order status transition (e.g. pending -> completed, cancelled -> processing, etc.).
     */
    public function filter_new_order_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return $enabled;
        }
        return $enabled && $this->allowing_admin_new_order_email;
    }

    /**
     * Only allow WC_Email_Customer_Processing_Order during initial checkout confirmation,
     * never when an order is moved to 'processing' (În procesare).
     */
    public function filter_customer_processing_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return $enabled;
        }
        return $enabled && $this->allowing_customer_new_order_email;
    }

    /**
     * Only allow WC_Email_Customer_Completed_Order ("...este pe drum!") when an order
     * transitions to 'shipped' (Predată Curier), never when moving to 'completed' (Finalizată (Livrată)).
     */
    public function filter_customer_completed_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return $enabled;
        }
        return $enabled && $this->allowing_customer_shipped_email;
    }

    /**
     * Only allow WC_Email_Customer_Cancelled_Order when explicitly triggered by on_order_status_cancelled()
     * so it is sent exactly once when transitioning from ANY status to 'cancelled' (Anulată).
     */
    public function filter_customer_cancelled_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return true;
        }
        return $this->allowing_customer_cancelled_email;
    }

    /**
     * Disable WC_Email_Cancelled_Order (admin cancellation email) on order status transitions
     * so cancelling an order does not send duplicate "Comandă anulată" emails.
     */
    public function filter_admin_cancelled_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return $enabled;
        }
        return false;
    }

    /**
     * Disable WC_Email_Customer_On_Hold_Order on order status transitions.
     */
    public function filter_customer_on_hold_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( null === $order ) {
            return $enabled;
        }
        return false;
    }

    /**
     * Locate custom WooCommerce email templates from the plugin's templates/ directory.
     */
    public function locate_email_template( string $template, string $template_name, string $template_path ): string {
        $custom = PCS_API_PLUGIN_DIR . 'templates/' . ltrim( $template_name, '/' );
        if ( file_exists( $custom ) ) {
            return $custom;
        }
        return $template;
    }

    /**
     * Locate custom core WooCommerce email templates.
     */
    public function locate_core_email_template( string $core_file, string $template, string $template_base ): string {
        $custom = PCS_API_PLUGIN_DIR . 'templates/' . ltrim( $template, '/' );
        if ( file_exists( $custom ) ) {
            return $custom;
        }
        return $core_file;
    }

    /**
     * Never display product images or SKUs in WooCommerce order emails.
     */
    public function filter_email_order_items_args( array $args ): array {
        $args['show_image'] = false;
        $args['show_sku']   = false;
        return $args;
    }

    /**
     * Replace custom email placeholders ({store_phone}, {phone_number}, {phone number}, {email})
     * and ensure the help contact message includes both phone number and email address.
     */
    public function filter_email_format_string( string $string, mixed $email = null ): string {
        $phone = PCS_Mailer::instance()->get_store_phone();
        $store_email = ( is_object( $email ) && method_exists( $email, 'get_from_address' ) )
            ? (string) $email->get_from_address()
            : PCS_Mailer::instance()->get_default_from_email();

        $string = str_replace(
            [ '{store_phone}', '{phone_number}', '{phone number}', '{email}' ],
            [ $phone, $phone, $phone, $store_email ],
            $string
        );

        // If any stored option still contains the old single-email sentence, upgrade it
        $string = preg_replace(
            '/Dacă ai nevoie de ajutor pentru comanda ta,\s*te rugăm să ne contactezi la\s+(?!num[ăa]rul de telefon)([^\s.<]+@[^\s.<]+)\.?\s*$/iu',
            "Dacă ai nevoie de ajutor pentru comanda ta, te rugăm să ne contactezi la numărul de telefon {$phone} sau la adresa de email: $1",
            $string
        );

        return $string;
    }

    /**
     * Customize WooCommerce Romanian strings ("Produs" capital P and help contact sentence with phone + email).
     */
    public function filter_woocommerce_gettext( string $translation, string $text, string $domain ): string {
        if ( 'woocommerce' !== $domain ) {
            return $translation;
        }
        if ( 'Product' === $text && 'produs' === $translation ) {
            return 'Produs';
        }
        if ( 'If you need any help with your order, please contact us at {store_email}.' === $text ) {
            return 'Dacă ai nevoie de ajutor pentru comanda ta, te rugăm să ne contactezi la numărul de telefon {store_phone} sau la adresa de email: {store_email}';
        }
        if ( 'Thanks again! If you need any help with your order, please contact us at {store_email}.' === $text ) {
            return 'Îți mulțumim din nou! Dacă ai nevoie de ajutor pentru comanda ta, te rugăm să ne contactezi la numărul de telefon {store_phone} sau la adresa de email: {store_email}';
        }
        if ( 'Thanks again! If you need any help with your order, please contact us at %s.' === $text ) {
            return 'Îți mulțumim din nou! Dacă ai nevoie de ajutor pentru comanda ta, te rugăm să ne contactezi la numărul de telefon {store_phone} sau la adresa de email: %s';
        }
        return $translation;
    }
}

PCS_API::instance();
