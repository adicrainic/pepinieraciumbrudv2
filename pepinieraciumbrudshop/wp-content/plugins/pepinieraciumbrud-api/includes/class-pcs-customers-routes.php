<?php
/**
 * Customer management routes.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Customers_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/customers', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'list_customers' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );

        register_rest_route( $this->namespace, '/admin/customers/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_customer' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );

        register_rest_route( $this->namespace, '/admin/customers/(?P<id>\d+)/orders', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_customer_orders' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );
    }

    public function list_customers( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = array_filter( [
            'page'     => $request->get_param( 'page' ) ?? 1,
            'per_page' => min( (int) ( $request->get_param( 'per_page' ) ?? 20 ), 100 ),
            'search'   => $request->get_param( 'search' ),
            'orderby'  => $request->get_param( 'orderby' ) ?? 'registered_date',
            'order'    => $request->get_param( 'order' ) ?? 'desc',
        ] );
        $result = $this->proxy()->get( 'customers', $params );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function get_customer( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( "customers/$id" );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function get_customer_orders( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( 'orders', [ 'customer' => $id, 'per_page' => 50 ] );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }
}

