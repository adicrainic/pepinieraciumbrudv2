<?php
/**
 * Contact form submission & admin messages management routes.
 *
 * Public:
 * POST /wp-json/pcs/v1/contact
 *
 * Admin:
 * GET    /wp-json/pcs/v1/admin/contact-messages
 * GET    /wp-json/pcs/v1/admin/contact-messages/{id}
 * PATCH  /wp-json/pcs/v1/admin/contact-messages/{id}
 * DELETE /wp-json/pcs/v1/admin/contact-messages/{id}
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Contact_Routes extends PCS_Base_Routes {

    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'contact_messages';
        $this->ensure_table();
    }

    /**
     * Create the contact messages table if not existing.
     */
    public function ensure_table(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            email varchar(191) NOT NULL,
            phone varchar(64) NOT NULL DEFAULT '',
            message longtext NOT NULL,
            status varchar(32) NOT NULL DEFAULT 'unread',
            email_sent tinyint(1) NOT NULL DEFAULT 0,
            ip_address varchar(45) NULL,
            user_agent varchar(255) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            read_at datetime NULL,
            reply_message longtext NULL,
            replied_at datetime NULL,
            replied_by varchar(191) NULL,
            PRIMARY KEY  (id),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Ensure reply columns exist for existing tables
        $has_reply = $wpdb->get_var( "SHOW COLUMNS FROM `{$this->table_name}` LIKE 'reply_message'" );
        if ( ! $has_reply ) {
            $wpdb->query( "ALTER TABLE `{$this->table_name}` ADD COLUMN reply_message longtext NULL, ADD COLUMN replied_at datetime NULL, ADD COLUMN replied_by varchar(191) NULL" );
        }
    }

    public function register(): void {
        // Public contact form submission
        register_rest_route( $this->namespace, '/contact', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'submit_contact_form' ],
                'permission_callback' => '__return_true',
            ],
        ] );

        // Admin: List messages
        register_rest_route( $this->namespace, '/admin/contact-messages', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_admin_messages' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        // Admin: Single message operations
        register_rest_route( $this->namespace, '/admin/contact-messages/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_admin_message' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => [ 'POST', 'PUT', 'PATCH' ],
                'callback'            => [ $this, 'update_admin_message' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete_admin_message' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        // Admin: Reply to message and email customer
        register_rest_route( $this->namespace, '/admin/contact-messages/(?P<id>\d+)/reply', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'reply_to_message' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    /**
     * Submit contact form (Storefront public endpoint).
     */
    public function submit_contact_form( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $params = $request->get_json_params();
        if ( empty( $params ) || ! is_array( $params ) ) {
            $params = $request->get_params();
        }

        // Honeypot spam protection
        $honeypot = trim( (string) ( $params['honeypot'] ?? $params['website'] ?? '' ) );
        if ( ! empty( $honeypot ) ) {
            // Silently pretend success to bots
            return $this->success( [
                'success' => true,
                'message' => 'Mesajul a fost recepționat.',
            ] );
        }

        $name    = sanitize_text_field( trim( (string) ( $params['name'] ?? '' ) ) );
        $email   = sanitize_email( trim( (string) ( $params['email'] ?? '' ) ) );
        $phone   = sanitize_text_field( trim( (string) ( $params['phone'] ?? '' ) ) );
        $message = sanitize_textarea_field( trim( (string) ( $params['message'] ?? '' ) ) );

        if ( empty( $name ) ) {
            return $this->error( 'pcs_contact_missing_name', 'Vă rugăm să introduceți numele și prenumele.', 400 );
        }

        if ( empty( $email ) || ! is_email( $email ) ) {
            return $this->error( 'pcs_contact_invalid_email', 'Vă rugăm să introduceți o adresă de email validă.', 400 );
        }

        if ( empty( $message ) ) {
            return $this->error( 'pcs_contact_missing_message', 'Vă rugăm să introduceți conținutul mesajului.', 400 );
        }

        $ip_address = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
        $user_agent = substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ), 0, 255 );

        // 1. Save to database
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'message'    => $message,
                'status'     => 'unread',
                'email_sent' => 0,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ]
        );

        if ( false === $inserted ) {
            return $this->error( 'pcs_db_error', 'Nu s-a putut salva mesajul. Vă rugăm să încercați din nou.', 500 );
        }

        $message_id = (int) $wpdb->insert_id;

        // 2. Dispatch notification email
        $email_sent = $this->send_contact_email( $message_id, $name, $email, $phone, $message );
        if ( $email_sent ) {
            $wpdb->update(
                $this->table_name,
                [ 'email_sent' => 1 ],
                [ 'id' => $message_id ],
                [ '%d' ],
                [ '%d' ]
            );
        }

        return $this->success( [
            'success'    => true,
            'message'    => 'Mesajul a fost trimis cu succes! Vă vom răspunde în cel mai scurt timp.',
            'message_id' => $message_id,
            'email_sent' => $email_sent,
        ] );
    }

    /**
     * Dispatch email notification to site admin/contact email.
     */
    private function send_contact_email( int $id, string $name, string $email, string $phone, string $message_content ): bool {
        global $wpdb;

        // Find recipient email from settings table or fallback to WP admin_email
        $settings_table = $wpdb->prefix . 'settings';
        $contact_email = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'contact_email' )
        );

        if ( empty( $contact_email ) || ! is_email( $contact_email ) ) {
            $contact_email = get_option( 'admin_email' );
        }

        if ( empty( $contact_email ) ) {
            return false;
        }

        $site_title = get_bloginfo( 'name' );
        if ( empty( $site_title ) || $site_title === 'WordPress' ) {
            $site_title = 'Pepiniera Ciumbrud';
        }

        $subject = sprintf( '[%s] Mesaj nou de la %s', $site_title, $name );

        $clean_phone = ! empty( $phone ) ? esc_html( $phone ) : '<em>Nespecificat</em>';
        $clean_name  = esc_html( $name );
        $clean_email = esc_html( $email );
        $body_text   = nl2br( esc_html( $message_content ) );
        $date_str    = date_i18n( 'j F Y, H:i', current_time( 'timestamp' ) );

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f7f9f6; color: #232c25; margin: 0; padding: 24px 12px; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e3e8e1; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="background-color: #245a38; color: #ffffff; padding: 24px 28px;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 600;">🌿 {$site_title}</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">Ai primit un mesaj nou prin formularul de contact</p>
        </div>
        <div style="padding: 28px;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0; color: #627265; width: 120px; font-weight: 600;">Nume:</td>
                    <td style="padding: 8px 0; color: #1c261e; font-weight: bold;">{$clean_name}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #627265; font-weight: 600;">Email:</td>
                    <td style="padding: 8px 0;"><a href="mailto:{$clean_email}" style="color: #245a38; text-decoration: underline; font-weight: 500;">{$clean_email}</a></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #627265; font-weight: 600;">Telefon:</td>
                    <td style="padding: 8px 0; color: #1c261e;">{$clean_phone}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #627265; font-weight: 600;">Data:</td>
                    <td style="padding: 8px 0; color: #1c261e;">{$date_str}</td>
                </tr>
            </table>

            <div style="background-color: #f7f9f6; border-radius: 12px; padding: 18px 20px; border-left: 4px solid #245a38; margin-bottom: 24px;">
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #627265; margin-bottom: 8px; letter-spacing: 0.5px;">Mesaj:</div>
                <div style="font-size: 15px; color: #1c261e; white-space: pre-line;">{$body_text}</div>
            </div>

            <div style="text-align: center; margin-top: 28px;">
                <a href="mailto:{$clean_email}?subject=Re:%20Mesaj%20Pepiniera%20Ciumbrud" style="display: inline-block; background-color: #245a38; color: #ffffff; text-decoration: none; padding: 12px 26px; border-radius: 10px; font-weight: 600; font-size: 14px;">
                    Răspunde prin Email
                </a>
            </div>
        </div>
        <div style="background-color: #f0f4ee; padding: 14px 28px; text-align: center; font-size: 11px; color: #768779; border-top: 1px solid #e3e8e1;">
            Mesajul #{$id} este stocat în panoul de administrare Pepiniera Ciumbrud.
        </div>
    </div>
</body>
</html>
HTML;

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'Reply-To: %s <%s>', $name, $email ),
        ];

        // Ensure from name matches site
        add_filter( 'wp_mail_from_name', function() use ( $site_title ) {
            return $site_title;
        } );

        try {
            return (bool) wp_mail( $contact_email, $subject, $html, $headers );
        } catch ( \Throwable $e ) {
            error_log( 'PCS Contact Form wp_mail error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Admin handler: GET /pcs/v1/admin/contact-messages
     */
    public function get_admin_messages( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $page     = max( 1, (int) $request->get_param( 'page' ) );
        $per_page = max( 1, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
        $offset   = ( $page - 1 ) * $per_page;

        $status   = sanitize_text_field( (string) $request->get_param( 'status' ) );
        $search   = sanitize_text_field( (string) $request->get_param( 'search' ) );

        $where = [ '1=1' ];
        $args  = [];

        if ( ! empty( $status ) && in_array( $status, [ 'unread', 'read', 'replied', 'archived' ], true ) ) {
            $where[] = 'status = %s';
            $args[]  = $status;
        }

        if ( ! empty( $search ) ) {
            $like    = '%' . $wpdb->esc_like( $search ) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR message LIKE %s)';
            $args[]  = $like;
            $args[]  = $like;
            $args[]  = $like;
            $args[]  = $like;
        }

        $where_sql = implode( ' AND ', $where );

        // Count total
        if ( ! empty( $args ) ) {
            $count_query = $wpdb->prepare( "SELECT COUNT(*) FROM `{$this->table_name}` WHERE {$where_sql}", $args );
        } else {
            $count_query = "SELECT COUNT(*) FROM `{$this->table_name}` WHERE {$where_sql}";
        }
        $total = (int) $wpdb->get_var( $count_query );

        // Fetch paginated items
        $query_sql = "SELECT id, name, email, phone, message, status, email_sent, ip_address, created_at, read_at, reply_message, replied_at, replied_by
                      FROM `{$this->table_name}`
                      WHERE {$where_sql}
                      ORDER BY id DESC
                      LIMIT %d OFFSET %d";
        $fetch_args = array_merge( $args, [ $per_page, $offset ] );
        $rows = $wpdb->get_results( $wpdb->prepare( $query_sql, $fetch_args ), ARRAY_A );

        // Unread count
        $unread_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table_name}` WHERE status = 'unread'" );

        return $this->success( [
            'items'        => $rows ?: [],
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $per_page,
            'total_pages'  => ceil( $total / $per_page ),
            'unread_count' => $unread_count,
        ] );
    }

    /**
     * Admin handler: GET /pcs/v1/admin/contact-messages/{id}
     */
    public function get_admin_message( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = (int) $request->get_param( 'id' );
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$this->table_name}` WHERE id = %d LIMIT 1", $id ),
            ARRAY_A
        );

        if ( ! $row ) {
            return $this->error( 'pcs_not_found', 'Mesajul nu a fost găsit.', 404 );
        }

        // Auto mark as read if currently unread
        if ( $row['status'] === 'unread' ) {
            $now = current_time( 'mysql' );
            $wpdb->update(
                $this->table_name,
                [ 'status' => 'read', 'read_at' => $now ],
                [ 'id' => $id ],
                [ '%s', '%s' ],
                [ '%d' ]
            );
            $row['status']  = 'read';
            $row['read_at'] = $now;
        }

        return $this->success( $row );
    }

    /**
     * Admin handler: PATCH/PUT/POST /pcs/v1/admin/contact-messages/{id}
     */
    public function update_admin_message( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = (int) $request->get_param( 'id' );
        $params = $request->get_json_params() ?: $request->get_params();

        $status = sanitize_text_field( (string) ( $params['status'] ?? '' ) );
        $valid_statuses = [ 'unread', 'read', 'replied', 'archived' ];

        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return $this->error( 'pcs_invalid_status', 'Status invalid.', 400 );
        }

        $update_data = [ 'status' => $status ];
        $update_format = [ '%s' ];

        if ( $status === 'read' ) {
            $update_data['read_at'] = current_time( 'mysql' );
            $update_format[] = '%s';
        }

        $wpdb->update(
            $this->table_name,
            $update_data,
            [ 'id' => $id ],
            $update_format,
            [ '%d' ]
        );

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$this->table_name}` WHERE id = %d LIMIT 1", $id ),
            ARRAY_A
        );

        return $this->success( [
            'message' => 'Statusul a fost actualizat cu succes.',
            'item'    => $row,
        ] );
    }

    /**
     * Admin handler: DELETE /pcs/v1/admin/contact-messages/{id}
     */
    public function delete_admin_message( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = (int) $request->get_param( 'id' );
        $deleted = $wpdb->delete(
            $this->table_name,
            [ 'id' => $id ],
            [ '%d' ]
        );

        if ( ! $deleted ) {
            return $this->error( 'pcs_delete_failed', 'Mesajul nu a putut fi șters sau nu există.', 404 );
        }

        return $this->success( [
            'message' => 'Mesajul a fost șters cu succes.',
            'id'      => $id,
        ] );
    }

    /**
     * Admin handler: POST /pcs/v1/admin/contact-messages/{id}/reply
     * Sends an email response to the customer and records the reply in database.
     */
    public function reply_to_message( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = (int) $request->get_param( 'id' );
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$this->table_name}` WHERE id = %d LIMIT 1", $id ),
            ARRAY_A
        );

        if ( ! $row ) {
            return $this->error( 'pcs_not_found', 'Mesajul nu a fost găsit.', 404 );
        }

        $params = $request->get_json_params() ?: $request->get_params();
        $reply_text = sanitize_textarea_field( trim( (string) ( $params['message'] ?? '' ) ) );
        $subject    = sanitize_text_field( trim( (string) ( $params['subject'] ?? '' ) ) );

        if ( empty( $reply_text ) ) {
            return $this->error( 'pcs_empty_reply', 'Vă rugăm să introduceți conținutul răspunsului.', 400 );
        }

        $site_title = get_bloginfo( 'name' );
        if ( empty( $site_title ) || $site_title === 'WordPress' ) {
            $site_title = 'Pepiniera Ciumbrud';
        }

        if ( empty( $subject ) ) {
            $subject = sprintf( 'Re: Răspuns de la %s', $site_title );
        }

        // Send email to customer
        $recipient_email = $row['email'];
        $recipient_name  = $row['name'];

        // Get sender/store contact email
        $settings_table = $wpdb->prefix . 'settings';
        $contact_email  = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'contact_email' )
        );
        if ( empty( $contact_email ) || ! is_email( $contact_email ) ) {
            $contact_email = get_option( 'admin_email' );
        }

        $body_html      = nl2br( esc_html( $reply_text ) );
        $original_text  = nl2br( esc_html( $row['message'] ) );
        $original_date  = date_i18n( 'j F Y, H:i', strtotime( $row['created_at'] ) );
        $current_user   = wp_get_current_user();
        $replied_by     = $current_user && $current_user->exists() ? ( $current_user->display_name ?: $current_user->user_login ) : 'Echipa Pepiniera Ciumbrud';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f7f9f6; color: #232c25; margin: 0; padding: 24px 12px; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e3e8e1; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="background-color: #245a38; color: #ffffff; padding: 24px 28px;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 600;">🌿 {$site_title}</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">Răspuns la solicitarea dumneavoastră</p>
        </div>
        <div style="padding: 28px;">
            <p style="font-size: 15px; margin-top: 0;">Bună ziua <strong>{$recipient_name}</strong>,</p>
            <div style="font-size: 15px; color: #1c261e; line-height: 1.7; margin: 18px 0 24px;">
                {$body_html}
            </div>

            <p style="font-size: 13px; color: #556557; margin-bottom: 24px;">
                Cu stimă,<br>
                <strong>{$replied_by}</strong><br>
                {$site_title}
            </p>

            <div style="background-color: #f7f9f6; border-radius: 12px; padding: 16px 20px; border-left: 4px solid #ccd8c9; margin-top: 24px;">
                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #768779; margin-bottom: 6px;">Mesajul dumneavoastră original ({$original_date}):</div>
                <div style="font-size: 13px; color: #556557; font-style: italic;">{$original_text}</div>
            </div>
        </div>
        <div style="background-color: #f0f4ee; padding: 14px 28px; text-align: center; font-size: 11px; color: #768779; border-top: 1px solid #e3e8e1;">
            Dacă aveți întrebări suplimentare, puteți răspunde direct la acest email sau ne puteți contacta la {$contact_email}.
        </div>
    </div>
</body>
</html>
HTML;

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $site_title, $contact_email ),
            sprintf( 'Reply-To: %s <%s>', $site_title, $contact_email ),
        ];

        // Ensure from name matches site
        add_filter( 'wp_mail_from_name', function() use ( $site_title ) {
            return $site_title;
        } );
        if ( is_email( $contact_email ) ) {
            add_filter( 'wp_mail_from', function() use ( $contact_email ) {
                return $contact_email;
            } );
        }

        $email_sent = false;
        try {
            $email_sent = (bool) wp_mail( $recipient_email, $subject, $html, $headers );
        } catch ( \Throwable $e ) {
            error_log( 'PCS Reply wp_mail error: ' . $e->getMessage() );
        }

        // Update database with reply
        $now = current_time( 'mysql' );
        $wpdb->update(
            $this->table_name,
            [
                'status'        => 'replied',
                'reply_message' => $reply_text,
                'replied_at'    => $now,
                'replied_by'    => $replied_by,
            ],
            [ 'id' => $id ],
            [ '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        $updated_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$this->table_name}` WHERE id = %d LIMIT 1", $id ),
            ARRAY_A
        );

        return $this->success( [
            'success'    => true,
            'message'    => 'Răspunsul a fost salvat' . ( $email_sent ? ' și expediat către client.' : ' (salvat în baza de date; expedierea pe email local depinde de serverul de mail).' ),
            'email_sent' => $email_sent,
            'item'       => $updated_row,
        ] );
    }
}
