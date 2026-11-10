<?php
/**
 * WooCommerce REST API proxy — calls WC API server-side, secrets never leave the server.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_WC_Proxy {

    private string $base_url;
    private string $consumer_key;
    private string $consumer_secret;

    public function __construct() {
        $this->base_url        = defined( 'PCS_WC_API_URL' ) ? PCS_WC_API_URL : home_url( '/wp-json/wc/v3' );
        $this->consumer_key    = defined( 'PCS_WC_CONSUMER_KEY' ) ? PCS_WC_CONSUMER_KEY : '';
        $this->consumer_secret = defined( 'PCS_WC_CONSUMER_SECRET' ) ? PCS_WC_CONSUMER_SECRET : '';
    }

    // -------------------------------------------------------------------------
    // Public methods
    // -------------------------------------------------------------------------

    public function get( string $endpoint, array $params = [] ): array|WP_Error {
        return $this->request( 'GET', $endpoint, [], $params );
    }

    public function post( string $endpoint, array $body = [] ): array|WP_Error {
        return $this->request( 'POST', $endpoint, $body );
    }

    public function put( string $endpoint, array $body = [] ): array|WP_Error {
        return $this->request( 'PUT', $endpoint, $body );
    }

    public function delete( string $endpoint, array $params = [] ): array|WP_Error {
        return $this->request( 'DELETE', $endpoint, [], $params );
    }

    // -------------------------------------------------------------------------
    // Core request
    // -------------------------------------------------------------------------

    private function request( string $method, string $endpoint, array $body = [], array $params = [] ): array|WP_Error {
        // Internal REST dispatch (fastest, no loopback HTTP/SSL overhead)
        $path = '/wc/v3/' . ltrim( $endpoint, '/' );
        $internal_req = new WP_REST_Request( $method, $path );
        if ( ! empty( $params ) ) {
            $internal_req->set_query_params( $params );
        }
        if ( ! empty( $body ) ) {
            $internal_req->set_body_params( $body );
            $internal_req->set_headers( [ 'content-type' => 'application/json' ] );
        }

        $server = rest_get_server();
        $response = $server->dispatch( $internal_req );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( $response->is_error() ) {
            return $response->as_error() ?: new WP_Error( 'pcs_wc_error', 'WooCommerce API error', [ 'status' => $response->get_status() ] );
        }

        $status = $response->get_status();
        $data = $server->response_to_data( $response, 'embed' );

        if ( $status >= 400 ) {
            $message = is_array( $data ) && isset( $data['message'] ) ? $data['message'] : 'WooCommerce API error';
            return new WP_Error( 'pcs_wc_error', $message, [ 'status' => $status ] );
        }

        return is_array( $data ) ? $data : [];
    }
}

