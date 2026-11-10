<?php
/**
 * Dashboard statistics routes.
 *
 * GET /wp-json/pcs/v1/admin/dashboard
 * GET /wp-json/pcs/v1/admin/inventory
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Dashboard_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/dashboard', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'dashboard' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );

        register_rest_route( $this->namespace, '/admin/inventory', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'inventory' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );

        register_rest_route( $this->namespace, '/admin/inventory/(?P<id>\d+)', [
            'methods'             => [ 'PUT', 'PATCH' ],
            'callback'            => [ $this, 'update_stock' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );
    }

    public function dashboard( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $proxy = $this->proxy();

        // Fetch data in parallel (sequential here but fast)
        $recent_orders   = $proxy->get( 'orders',   [ 'per_page' => 5, 'orderby' => 'date', 'order' => 'desc' ] );
        $top_sellers     = $proxy->get( 'reports/top_sellers', [ 'period' => 'month' ] );
        $sales_report    = $proxy->get( 'reports/sales',  [ 'period' => 'month' ] );
        $products_total  = $proxy->get( 'reports/products/totals' );
        $orders_total    = $proxy->get( 'reports/orders/totals' );
        $customers_total = $proxy->get( 'reports/customers/totals' );

        // Collect any errors but don't fail completely
        $stats = [
            'recent_orders'   => is_wp_error( $recent_orders )   ? [] : $recent_orders,
            'top_sellers'     => is_wp_error( $top_sellers )     ? [] : $top_sellers,
            'sales'           => is_wp_error( $sales_report )    ? [] : $sales_report,
            'products_total'  => is_wp_error( $products_total )  ? [] : $products_total,
            'orders_total'    => is_wp_error( $orders_total )    ? [] : $orders_total,
            'customers_total' => is_wp_error( $customers_total ) ? [] : $customers_total,
        ];

        return $this->success( $stats );
    }

    public function inventory( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = [
            'per_page' => 100,
        ];

        $stock_status = $request->get_param( 'stock_status' );
        if ( ! empty( $stock_status ) && in_array( $stock_status, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
            $params['stock_status'] = $stock_status;
        }

        $manage_stock = $request->get_param( 'manage_stock' );
        if ( null !== $manage_stock && '' !== $manage_stock ) {
            $params['manage_stock'] = rest_sanitize_boolean( $manage_stock );
        }

        $search = $request->get_param( 'search' );
        if ( ! empty( $search ) ) {
            $params['search'] = sanitize_text_field( $search );
        }

        $result = $this->proxy()->get( 'products', $params );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( ! is_array( $result ) ) {
            $result = [];
        }

        $default_low_stock = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );

        $inventory = array_map( function( $p ) use ( $default_low_stock ) {
            return $this->format_inventory_item( $p, $default_low_stock );
        }, $result );

        return $this->success( $inventory );
    }

    public function update_stock( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        if ( ! $id ) {
            return new WP_Error( 'invalid_id', 'ID produs invalid.', [ 'status' => 400 ] );
        }

        $payload = [];

        if ( $request->has_param( 'manage_stock' ) ) {
            $payload['manage_stock'] = rest_sanitize_boolean( $request->get_param( 'manage_stock' ) );
        }

        if ( $request->has_param( 'stock_quantity' ) ) {
            $qty = $request->get_param( 'stock_quantity' );
            if ( null === $qty || '' === $qty ) {
                $payload['stock_quantity'] = null;
            } else {
                $payload['stock_quantity'] = (int) $qty;
                if ( ! isset( $payload['manage_stock'] ) ) {
                    $payload['manage_stock'] = true;
                }
            }
        }

        if ( $request->has_param( 'low_stock_amount' ) ) {
            $low = $request->get_param( 'low_stock_amount' );
            if ( null === $low || '' === $low ) {
                $payload['low_stock_amount'] = null;
            } else {
                $payload['low_stock_amount'] = max( 0, (int) $low );
            }
        }

        if ( $request->has_param( 'stock_status' ) ) {
            $status = sanitize_text_field( $request->get_param( 'stock_status' ) );
            if ( in_array( $status, [ 'instock', 'outofstock', 'onbackorder' ], true ) ) {
                $payload['stock_status'] = $status;
            }
        } elseif ( isset( $payload['stock_quantity'] ) ) {
            if ( $payload['stock_quantity'] <= 0 ) {
                $payload['stock_status'] = 'outofstock';
            } else {
                $payload['stock_status'] = 'instock';
            }
        }

        if ( empty( $payload ) ) {
            return new WP_Error( 'empty_payload', 'Nu s-au furnizat câmpuri de actualizat.', [ 'status' => 400 ] );
        }

        $result = $this->proxy()->put( "products/$id", $payload );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $default_low_stock = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
        $formatted = $this->format_inventory_item( $result, $default_low_stock );

        return $this->success( $formatted );
    }

    private function format_inventory_item( array $p, int $default_low_stock ): array {
        $qty = isset( $p['stock_quantity'] ) && null !== $p['stock_quantity'] ? (int) $p['stock_quantity'] : null;
        $manage_stock = ! empty( $p['manage_stock'] );
        $custom_low_stock = isset( $p['low_stock_amount'] ) && '' !== $p['low_stock_amount'] && null !== $p['low_stock_amount']
            ? (int) $p['low_stock_amount']
            : null;
        $effective_threshold = null !== $custom_low_stock ? $custom_low_stock : $default_low_stock;
        $is_low = ( $p['stock_status'] ?? '' ) === 'outofstock' || ( $manage_stock && null !== $qty && $qty <= $effective_threshold );

        return [
            'id'                  => (int) ( $p['id'] ?? 0 ),
            'name'                => $p['name'] ?? '',
            'sku'                 => $p['sku'] ?? '',
            'stock_status'        => $p['stock_status'] ?? 'outofstock',
            'stock_quantity'      => $qty,
            'manage_stock'        => $manage_stock,
            'low_stock_amount'    => $custom_low_stock,
            'effective_threshold' => $effective_threshold,
            'low_stock'           => $is_low,
            'price'               => $p['price'] ?? '',
            'permalink'           => $p['permalink'] ?? '',
            'image'               => ! empty( $p['images'][0]['src'] ) ? $p['images'][0]['src'] : null,
        ];
    }
}

