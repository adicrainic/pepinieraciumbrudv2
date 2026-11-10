<?php
/**
 * Authentication routes: login, logout, refresh, me.
 *
 * POST /wp-json/pcs/v1/admin/auth/login
 * POST /wp-json/pcs/v1/admin/auth/logout
 * GET  /wp-json/pcs/v1/admin/auth/me
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Auth_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/auth/login', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'login' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'username' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_user' ],
                'password' => [ 'required' => true, 'type' => 'string' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/auth/logout', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'logout' ],
            'permission_callback' => '__return_true',
        ] );

        register_rest_route( $this->namespace, '/admin/auth/me', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'me' ],
            'permission_callback' => [ $this, 'require_admin' ],
        ] );
    }

    // -------------------------------------------------------------------------

    public function login( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $username = $request->get_param( 'username' );
        $password = $request->get_param( 'password' );

        // Rate limiting: max 5 attempts per 60s per IP
        $ip_key    = 'pcs_login_attempts_' . md5( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
        $attempts  = (int) get_transient( $ip_key );

        if ( $attempts >= 5 ) {
            return $this->error( 'pcs_rate_limit', 'Too many login attempts. Please wait 60 seconds.', 429 );
        }

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            set_transient( $ip_key, $attempts + 1, 60 );
            // Generic message — do not reveal whether username or password was wrong
            return $this->error( 'pcs_auth_failed', 'Date de autentificare incorecte.', 401 );
        }

        if ( ! user_can( $user, 'manage_woocommerce' ) ) {
            return $this->error( 'pcs_forbidden', 'Acces interzis. Rolul de administrator este necesar.', 403 );
        }

        // Clear rate limit on success
        delete_transient( $ip_key );

        $token = PCS_JWT::create( [
            'user_id'   => $user->ID,
            'user_login' => $user->user_login,
            'role'      => 'administrator',
        ] );

        return $this->success( [
            'token' => $token,
            'user'  => [
                'id'         => $user->ID,
                'username'   => $user->user_login,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'role'       => 'administrator',
            ],
            'expires_in' => 28800,
        ] );
    }

    public function logout( WP_REST_Request $request ): WP_REST_Response {
        // JWT is stateless; logout is handled client-side by discarding the token.
        // Optionally a token blacklist could be implemented here using transients.
        return $this->success( [ 'message' => 'Deconectat cu succes.' ] );
    }

    public function me( WP_REST_Request $request ): WP_REST_Response {
        $user = wp_get_current_user();

        return $this->success( [
            'id'         => $user->ID,
            'username'   => $user->user_login,
            'email'      => $user->user_email,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'role'       => 'administrator',
        ] );
    }
}

