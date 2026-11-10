<?php
/**
 * Order management routes.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Orders_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/orders', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'list_orders' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );

        register_rest_route( $this->namespace, '/admin/orders/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_order' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update_order' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/orders/(?P<id>\d+)/notes', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_order_notes' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'add_order_note' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    public function list_orders( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = array_filter( [
            'page'     => $request->get_param( 'page' ) ?? 1,
            'per_page' => min( (int) ( $request->get_param( 'per_page' ) ?? 20 ), 100 ),
            'status'   => $request->get_param( 'status' ) ?? 'any',
            'orderby'  => $request->get_param( 'orderby' ) ?? 'date',
            'order'    => $request->get_param( 'order' ) ?? 'desc',
            'search'   => $request->get_param( 'search' ),
            'customer' => $request->get_param( 'customer' ),
            'after'    => $request->get_param( 'after' ),
            'before'   => $request->get_param( 'before' ),
        ] );
        $result = $this->proxy()->get( 'orders', $params );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function get_order( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( "orders/$id" );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function update_order( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $allowed = [ 'status', 'billing', 'shipping' ];
        $body = [];
        foreach ( $allowed as $key ) {
            $val = $request->get_param( $key );
            if ( null !== $val ) $body[ $key ] = $val;
        }
        if ( isset( $body['status'] ) ) $body['status'] = sanitize_key( $body['status'] );
        $result = $this->proxy()->put( "orders/$id", $body );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function get_order_notes( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( "orders/$id/notes" );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function add_order_note( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id   = (int) $request->get_param( 'id' );
        $note = sanitize_textarea_field( $request->get_param( 'note' ) ?? '' );
        if ( ! $note ) {
            return $this->error( 'pcs_missing_note', 'Nota este obligatorie.', 400 );
        }
        $result = $this->proxy()->post( "orders/$id/notes", [
            'note'             => $note,
            'customer_note'    => (bool) $request->get_param( 'customer_note' ),
            'added_by_user'    => true,
        ] );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result, 201 );
    }
}

