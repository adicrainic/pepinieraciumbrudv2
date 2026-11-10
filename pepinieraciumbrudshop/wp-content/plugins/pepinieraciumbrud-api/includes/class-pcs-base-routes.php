<?php
/**
 * Abstract base for all PCS admin REST routes.
 *
 * Provides shared authentication middleware that verifies the JWT token
 * and confirms the WordPress user holds the 'manage_woocommerce' capability.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

abstract class PCS_Base_Routes {

    protected string $namespace = PCS_API_NAMESPACE;

    abstract public function register(): void;

    // -------------------------------------------------------------------------
    // Shared permission callback
    // -------------------------------------------------------------------------

    /**
     * Verify the request carries a valid JWT token issued for an admin user.
     */
    public function require_admin( WP_REST_Request $request ): bool|WP_Error {
        $auth_header = $request->get_header( 'Authorization' );
        $token_header = $request->get_header( 'X-PCS-Token' );

        $raw_token = null;

        if ( $auth_header && str_starts_with( $auth_header, 'Bearer ' ) ) {
            $raw_token = substr( $auth_header, 7 );
        } elseif ( $token_header ) {
            $raw_token = $token_header;
        }

        if ( ! $raw_token ) {
            return new WP_Error( 'pcs_no_token', 'Authentication token required', [ 'status' => 401 ] );
        }

        $payload = PCS_JWT::validate( $raw_token );

        if ( is_wp_error( $payload ) ) {
            return $payload;
        }

        // Verify user still exists and is admin
        $user_id = $payload['user_id'] ?? 0;
        $user    = get_user_by( 'id', (int) $user_id );

        if ( ! $user || ! user_can( $user, 'manage_woocommerce' ) ) {
            return new WP_Error( 'pcs_forbidden', 'Insufficient permissions', [ 'status' => 403 ] );
        }

        // Set current user for this request context
        wp_set_current_user( $user->ID );

        return true;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function proxy(): PCS_WC_Proxy {
        return new PCS_WC_Proxy();
    }

    protected function success( $data, int $status = 200 ): WP_REST_Response {
        return new WP_REST_Response( $data, $status );
    }

    protected function error( string $code, string $message, int $status = 400 ): WP_Error {
        return new WP_Error( $code, $message, [ 'status' => $status ] );
    }
}

