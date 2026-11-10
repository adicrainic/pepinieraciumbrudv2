<?php
/**
 * Lightweight JWT implementation (HS256) — no external dependencies.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_JWT {

    private static function secret(): string {
        return defined( 'PCS_JWT_SECRET' ) ? PCS_JWT_SECRET : wp_salt( 'auth' );
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Create a signed JWT token.
     *
     * @param array $payload  Custom payload claims.
     * @param int   $ttl_sec  Token lifetime in seconds (default 8 hours).
     */
    public static function create( array $payload, int $ttl_sec = 28800 ): string {
        $now = time();

        $claims = array_merge( $payload, [
            'iat' => $now,
            'exp' => $now + $ttl_sec,
            'iss' => home_url(),
        ] );

        $header  = self::b64_encode( wp_json_encode( [ 'alg' => 'HS256', 'typ' => 'JWT' ] ) );
        $body    = self::b64_encode( wp_json_encode( $claims ) );
        $sig     = self::sign( "$header.$body" );

        return "$header.$body.$sig";
    }

    /**
     * Validate a JWT token.
     *
     * @param  string $token Raw token string.
     * @return array|WP_Error Decoded payload on success, WP_Error on failure.
     */
    public static function validate( string $token ): array|WP_Error {
        $parts = explode( '.', $token );

        if ( 3 !== count( $parts ) ) {
            return new WP_Error( 'pcs_jwt_malformed', 'Token malformat', [ 'status' => 401 ] );
        }

        [ $header, $body, $sig ] = $parts;

        $expected = self::sign( "$header.$body" );

        if ( ! hash_equals( $expected, $sig ) ) {
            return new WP_Error( 'pcs_jwt_invalid_sig', 'Invalid token signature', [ 'status' => 401 ] );
        }

        $payload = json_decode( self::b64_decode( $body ), true );

        if ( ! $payload ) {
            return new WP_Error( 'pcs_jwt_decode_fail', 'Could not decode token', [ 'status' => 401 ] );
        }

        if ( isset( $payload['exp'] ) && $payload['exp'] < time() ) {
            return new WP_Error( 'pcs_jwt_expired', 'Token expired', [ 'status' => 401 ] );
        }

        return $payload;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function sign( string $data ): string {
        return self::b64_encode( hash_hmac( 'sha256', $data, self::secret(), true ) );
    }

    private static function b64_encode( string $data ): string {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }

    private static function b64_decode( string $data ): string {
        $pad = strlen( $data ) % 4;
        if ( $pad ) {
            $data .= str_repeat( '=', 4 - $pad );
        }
        return base64_decode( strtr( $data, '-_', '+/' ) );
    }
}

