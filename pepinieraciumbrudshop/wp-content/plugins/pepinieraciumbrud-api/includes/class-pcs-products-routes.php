<?php
/**
 * Product management routes — secure proxy to WC REST API v3.
 *
 * GET    /wp-json/pcs/v1/admin/products
 * POST   /wp-json/pcs/v1/admin/products
 * GET    /wp-json/pcs/v1/admin/products/{id}
 * PUT    /wp-json/pcs/v1/admin/products/{id}
 * DELETE /wp-json/pcs/v1/admin/products/{id}
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Products_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/products', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'list_products' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'create_product' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/products/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_product' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update_product' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete_product' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    public function list_products( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = array_filter( [
            'page'      => $request->get_param( 'page' ) ?? 1,
            'per_page'  => min( (int) ( $request->get_param( 'per_page' ) ?? 20 ), 100 ),
            'search'    => $request->get_param( 'search' ),
            'category'  => $request->get_param( 'category' ),
            'status'    => $request->get_param( 'status' ) ?? 'any',
            'orderby'   => $request->get_param( 'orderby' ) ?? 'date',
            'order'     => $request->get_param( 'order' ) ?? 'desc',
            'stock_status' => $request->get_param( 'stock_status' ),
        ] );

        $result = $this->proxy()->get( 'products', $params );
        if ( is_wp_error( $result ) ) return $result;

        return $this->success( $result );
    }

    public function get_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( "products/$id" );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function create_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $body = $this->sanitize_product_body( $request );
        $result = $this->proxy()->post( 'products', $body );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result, 201 );
    }

    public function update_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id   = (int) $request->get_param( 'id' );
        $body = $this->sanitize_product_body( $request );
        $result = $this->proxy()->put( "products/$id", $body );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function delete_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->delete( "products/$id", [ 'force' => true ] );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( [ 'deleted' => true, 'id' => $id ] );
    }

    private function sanitize_product_body( WP_REST_Request $request ): array {
        $allowed_keys = [
            'name', 'type', 'status', 'description', 'short_description',
            'sku', 'regular_price', 'sale_price', 'stock_quantity',
            'manage_stock', 'stock_status', 'low_stock_amount', 'categories', 'images',
            'featured', 'catalog_visibility', 'weight', 'dimensions',
            'attributes', 'meta_data',
        ];

        $body = [];
        foreach ( $allowed_keys as $key ) {
            $val = $request->get_param( $key );
            if ( null !== $val ) {
                $body[ $key ] = $val;
            }
        }

        // Sanitize text fields
        foreach ( [ 'name', 'sku', 'status' ] as $text_field ) {
            if ( isset( $body[ $text_field ] ) ) {
                $body[ $text_field ] = sanitize_text_field( $body[ $text_field ] );
            }
        }

        if ( isset( $body['description'] ) ) {
            $body['description'] = wp_kses_post( $body['description'] );
        }

        return $body;
    }
}

