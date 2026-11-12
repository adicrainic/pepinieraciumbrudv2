<?php
/**
 * Category management routes.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Categories_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/categories', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'list_categories' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'create_category' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/categories/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_category' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update_category' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete_category' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    public function list_categories( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $params = [
            'per_page' => 100,
            'orderby'  => 'name',
            'order'    => 'asc',
            'hide_empty' => false,
        ];
        $result = $this->proxy()->get( 'products/categories', $params );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function get_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->get( "products/categories/$id" );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function create_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $body = $this->sanitize_category_body( $request );
        $result = $this->proxy()->post( 'products/categories', $body );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result, 201 );
    }

    public function update_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id   = (int) $request->get_param( 'id' );
        $body = $this->sanitize_category_body( $request );
        $result = $this->proxy()->put( "products/categories/$id", $body );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( $result );
    }

    public function delete_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $result = $this->proxy()->delete( "products/categories/$id", [ 'force' => true ] );
        if ( is_wp_error( $result ) ) return $result;
        return $this->success( [ 'deleted' => true, 'id' => $id ] );
    }

    private function sanitize_category_body( WP_REST_Request $request ): array {
        $body = [];
        $fields = [ 'name', 'slug', 'parent', 'description', 'display' ];
        foreach ( $fields as $field ) {
            $val = $request->get_param( $field );
            if ( null !== $val ) {
                $body[ $field ] = $val;
            }
        }
        if ( isset( $body['name'] ) ) $body['name'] = sanitize_text_field( $body['name'] );
        if ( isset( $body['slug'] ) ) $body['slug'] = sanitize_title( $body['slug'] );
        if ( isset( $body['parent'] ) ) $body['parent'] = (int) $body['parent'];
        if ( isset( $body['description'] ) ) $body['description'] = wp_kses_post( $body['description'] );

        // Handle category image
        if ( $request->has_param( 'image' ) ) {
            $image = $request->get_param( 'image' );
            if ( empty( $image ) || ! is_array( $image ) ) {
                $body['image'] = [ 'id' => 0 ];
            } elseif ( ! empty( $image['id'] ) ) {
                $body['image'] = [
                    'id' => absint( $image['id'] ),
                ];
                if ( ! empty( $image['src'] ) ) {
                    $body['image']['src'] = esc_url_raw( $image['src'] );
                }
            } elseif ( ! empty( $image['src'] ) ) {
                $body['image'] = [
                    'src' => esc_url_raw( $image['src'] ),
                ];
            } else {
                $body['image'] = [ 'id' => 0 ];
            }
        }

        return $body;
    }
}

