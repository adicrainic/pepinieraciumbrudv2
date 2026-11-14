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

        // Allow bulk / wholesale purchasing (quantities > 500)
        add_filter( 'woocommerce_store_api_product_quantity_maximum', fn() => 99999 );
        add_filter( 'woocommerce_store_api_product_quantity_limit', fn() => 99999 );
        add_filter( 'woocommerce_product_backorders_allowed', '__return_true' );

        // New orders status & lifecycle:
        // Set new orders default and COD status to 'pending' (În așteptare)
        add_filter( 'woocommerce_cod_process_payment_order_status', [ $this, 'filter_new_order_status' ], 10, 2 );
        add_filter( 'woocommerce_default_order_status', [ $this, 'filter_default_order_status' ] );

        // Prevent automatic cancellation of unpaid orders after hold_stock_minutes
        add_filter( 'woocommerce_cancel_unpaid_order', '__return_false' );

        // Stock management for pending orders
        remove_action( 'woocommerce_order_status_pending', 'wc_maybe_increase_stock_levels' );
        add_action( 'woocommerce_order_status_pending', 'wc_maybe_reduce_stock_levels' );

        // Handle checkout order processing (Store API & classic checkout)
        add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'on_checkout_order_processed' ], 20, 1 );
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'on_checkout_order_processed' ], 20, 1 );

        // Prevent duplicate customer confirmation email when transitioning from pending to processing
        add_filter( 'woocommerce_email_enabled_customer_processing_order', [ $this, 'filter_customer_processing_email_enabled' ], 10, 2 );
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
     * Ensure CORS headers are also present on pre-served REST responses.
     */
    public function cors_pre_serve( bool $served ): bool {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ( $origin && $this->is_allowed_origin( $origin ) ) {
            header( 'Access-Control-Allow-Origin: ' . $origin );
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Expose-Headers: ' . $this->get_cors_expose_headers() );
            header( 'Vary: Origin' );
        }
        return $served;
    }

    /**
     * Filter new order status for COD payments to 'pending' (În așteptare).
     */
    public function filter_new_order_status( string $status, ?WC_Order $order = null ): string {
        return 'pending';
    }

    /**
     * Filter default order status to 'pending' (În așteptare).
     */
    public function filter_default_order_status( string $status ): string {
        return 'pending';
    }

    /**
     * Handle checkout order processing for new orders:
     * - Reduce stock immediately
     * - Add note for COD payment
     * - Trigger admin "New order" email notification
     * - Trigger customer "Order confirmation" email notification
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
        if ( isset( $emails['WC_Email_New_Order'] ) && $emails['WC_Email_New_Order']->is_enabled() ) {
            if ( ! $order->get_new_order_email_sent() ) {
                $emails['WC_Email_New_Order']->trigger( $order_id, $order );
            }
        }

        // 4. Trigger Customer Order Received / Confirmation email
        if ( isset( $emails['WC_Email_Customer_Processing_Order'] ) && $emails['WC_Email_Customer_Processing_Order']->is_enabled() ) {
            if ( ! $order->get_meta( '_pcs_customer_order_email_sent' ) ) {
                $emails['WC_Email_Customer_Processing_Order']->trigger( $order_id, $order );
                $order->update_meta_data( '_pcs_customer_order_email_sent', 'yes' );
                $order->save();
            }
        }
    }

    /**
     * Prevent duplicate customer confirmation email when an order is transitioned
     * from 'pending' to 'processing' by store staff in the admin panel.
     */
    public function filter_customer_processing_email_enabled( bool $enabled, mixed $order = null ): bool {
        if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_pcs_customer_order_email_sent' ) && did_action( 'woocommerce_order_status_pending_to_processing_notification' ) ) {
            return false;
        }
        return $enabled;
    }
}

PCS_API::instance();
