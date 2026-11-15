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

        add_filter( 'woocommerce_rest_prepare_order_note', [ $this, 'filter_rest_prepare_order_note' ], 10, 3 );
    }

    public function filter_rest_prepare_order_note( $response, $note, $request ) {
        if ( isset( $response->data['note'] ) && is_string( $response->data['note'] ) ) {
            $response->data['note'] = self::translate_order_note_text( $response->data['note'] );
        }
        return $response;
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

        if ( is_array( $result ) ) {
            foreach ( $result as &$item ) {
                if ( isset( $item['note'] ) && is_string( $item['note'] ) ) {
                    $item['note'] = self::translate_order_note_text( $item['note'] );
                }
            }
            unset( $item );
        }

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
        if ( is_array( $result ) && isset( $result['note'] ) ) {
            $result['note'] = self::translate_order_note_text( $result['note'] );
        }
        return $this->success( $result, 201 );
    }

    /**
     * Translate order note strings from English to Romanian.
     */
    public static function translate_order_note_text( string $note ): string {
        $status_map = [
            'Pending payment'       => 'În așteptarea plății',
            'Pending'               => 'În așteptare (Nouă)',
            'Processing'            => 'În procesare',
            'Prepared'              => 'Procesată (Pregătită)',
            'Procesată (Pregătită)' => 'Procesată (Pregătită)',
            'Shipped'               => 'Predată Curier',
            'Predată Curier'        => 'Predată Curier',
            'On hold'               => 'În așteptare plată',
            'On-hold'               => 'În așteptare plată',
            'Completed'             => 'Finalizată (Livrată)',
            'Finalizată'            => 'Finalizată (Livrată)',
            'Cancelled'             => 'Anulată',
            'Refunded'              => 'Rambursată',
            'Failed'                => 'Eșuată',
            'Checkout draft'        => 'Ciornă finalizare',
            'Plată în așteptare'    => 'În așteptare (Nouă)',
        ];

        $email_map = [
            'Processing order'       => 'Comandă Nouă',
            'Comandă în procesare'   => 'Comandă Nouă',
            'New order'              => 'Comandă Nouă',
            'Comandă nouă'           => 'Comandă Nouă',
            'Comandă Nouă'           => 'Comandă Nouă',
            'Comandă Noua'           => 'Comandă Nouă',
            'Completed order'        => 'Comandă finalizată',
            'Cancelled order'        => 'Comandă anulată',
            'Failed order'           => 'Comandă eșuată',
            'Customer on-hold order' => 'Comandă în așteptare',
            'Customer note'          => 'Notă client',
            'Customer invoice'       => 'Factură client',
            'Reset password'         => 'Resetare parolă',
            'New account'            => 'Cont nou',
        ];

        $t = $note;

        // Order status changed from X to Y.
        $t = preg_replace_callback(
            '/(?:Order status changed from|Am schimbat starea comenzii din)\s+([^.]+?)\s+(?:to|în)\s+([^.]+?)\./i',
            function( $m ) use ( $status_map ) {
                $from = $status_map[ trim( $m[1] ) ] ?? $m[1];
                $to   = $status_map[ trim( $m[2] ) ] ?? $m[2];
                return "Starea comenzii a fost modificată din '{$from}' în '{$to}'.";
            },
            $t
        );

        // Stock hold
        $t = preg_replace( '/Stock hold of (\d+) minutes applied to:/i', 'Rezervare stoc timp de $1 minute aplicată pentru:', $t );

        // Stock levels reduced
        $t = preg_replace( '/(?:Stock levels reduced|Nivelurile stocurilor s-au redus):\s*(.*)/i', 'Nivel stoc redus: $1', $t );

        // Stock levels increased
        $t = preg_replace( '/(?:Stock levels increased|Nivelurile stocurilor au crescut):\s*(.*)/i', 'Nivel stoc restabilit: $1', $t );

        // Payment upon delivery
        $t = str_ireplace( 'Payment to be made upon delivery.', 'Plată ramburs la livrare.', $t );
        $t = str_ireplace( 'Payment to be made upon delivery', 'Plată ramburs la livrare', $t );
        $t = str_ireplace( 'Plata care se va face la livrare.', 'Plată ramburs la livrare.', $t );

        // Email sent
        $t = preg_replace_callback(
            '/(?:Email\s*\"([^\"]+)\"\s*sent\.|Emailul\s*[„«]([^”»]+)[”»]\s*(?:este|a fost) trimis\.)/iu',
            function( $m ) use ( $email_map ) {
                $raw = ! empty( $m[1] ) ? $m[1] : ( $m[2] ?? '' );
                $name = $email_map[ trim( $raw ) ] ?? $raw;
                return "Emailul «{$name}» a fost trimis.";
            },
            $t
        );

        // Email failed to send
        $t = preg_replace_callback(
            '/Email \"([^\"]+)\" failed to send:\s*(.*)/i',
            function( $m ) use ( $email_map ) {
                $name   = $email_map[ trim( $m[1] ) ] ?? $m[1];
                $reason = trim( $m[2] );
                if ( stripos( $reason, 'Could not instantiate mail function' ) !== false ) {
                    $reason = 'Funcția de mail nu a putut fi inițializată (SMTP neconfigurat la acel moment).';
                }
                return "Emailul «{$name}» nu a putut fi trimis: {$reason}";
            },
            $t
        );

        // Unpaid order cancelled
        $t = str_ireplace(
            'Unpaid order cancelled - time limit reached.',
            'Comanda neplătită a fost anulată automat (timp expirat).',
            $t
        );

        // Awaiting payments
        $t = str_ireplace( 'Awaiting cash on delivery payment', 'În așteptarea plății ramburs la livrare', $t );
        $t = str_ireplace( 'Awaiting BACS payment', 'În așteptarea transferului bancar', $t );
        $t = str_ireplace( 'Awaiting cheque payment', 'În așteptarea plății prin cec', $t );

        // Added by customer during checkout
        $t = str_ireplace( 'Added by customer during checkout:', 'Adăugată de client la plasarea comenzii:', $t );

        return $t;
    }
}

