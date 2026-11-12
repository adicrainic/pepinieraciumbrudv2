<?php
/**
 * Site settings routes for storefront customization.
 *
 * GET  /wp-json/pcs/v1/settings        (Public - for storefront)
 * GET  /wp-json/pcs/v1/admin/settings  (Admin only)
 * POST /wp-json/pcs/v1/admin/settings  (Admin only)
 * PUT  /wp-json/pcs/v1/admin/settings  (Admin only)
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Settings_Routes extends PCS_Base_Routes {

    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'settings';
        $this->ensure_table();
    }

    /**
     * Ensure the settings table exists in the database.
     */
    public function ensure_table(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `setting_key` VARCHAR(191) NOT NULL,
            `setting_value` LONGTEXT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_setting_key` (`setting_key`)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Ensure default settings exist
        $defaults = $this->get_default_settings();
        foreach ( $defaults as $key => $default_value ) {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT IGNORE INTO `{$this->table_name}` (`setting_key`, `setting_value`) VALUES (%s, %s)",
                    $key,
                    $default_value
                )
            );
        }
    }

    /**
     * Default settings dictionary.
     */
    private function get_default_settings(): array {
        return [
            'site_title'         => 'Pepiniera Ciumbrud',
            'site_tagline'       => 'Tradiție & Calitate din 1990',
            'site_description'   => 'Producător autorizat de material săditor pomicol, trandafiri și plante ornamentale de cea mai bună calitate, aclimatizate pentru România.',
            'logo_url'           => '',
            'contact_phone'      => '+40 700 000 000',
            'contact_email'      => 'contact@pepinieraciumbrud.ro',
            'contact_address'    => 'Sat Ciumbrud, Jud. Alba, România',
            'schedule_weekdays'  => 'Luni – Vineri: 08:00 – 17:00',
            'schedule_saturday'  => 'Sâmbătă: 08:00 – 13:00',
            'schedule_sunday'    => 'Duminică: Închis',
            'schedule_notes'     => 'Comenzile se pot plasa direct prin magazinul online la orice oră. Expedierea se face în ordinea primirii comenzilor.',
            'footer_copyright'   => 'Pepiniera Ciumbrud. Toate drepturile rezervate.',
        ];
    }

    public function register(): void {
        // Public settings route for storefront
        register_rest_route( $this->namespace, '/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_public_settings' ],
                'permission_callback' => '__return_true',
            ],
        ] );

        // Admin settings route
        register_rest_route( $this->namespace, '/admin/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_admin_settings' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
            [
                'methods'             => [ 'POST', 'PUT' ],
                'callback'            => [ $this, 'update_settings' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        // Admin email routes
        register_rest_route( $this->namespace, '/admin/email/status', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_email_status' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/email/test', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'send_test_email' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    /**
     * Get all settings from database.
     */
    private function fetch_all_settings(): array {
        global $wpdb;

        $rows = $wpdb->get_results( "SELECT setting_key, setting_value FROM `{$this->table_name}`", ARRAY_A );
        $settings = $this->get_default_settings();

        if ( ! empty( $rows ) && is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $settings[ $row['setting_key'] ] = $row['setting_value'] ?? '';
            }
        }

        return $settings;
    }

    /**
     * Public handler: GET /pcs/v1/settings
     */
    public function get_public_settings( WP_REST_Request $request ): WP_REST_Response {
        return $this->success( $this->fetch_all_settings() );
    }

    /**
     * Admin handler: GET /pcs/v1/admin/settings
     */
    public function get_admin_settings( WP_REST_Request $request ): WP_REST_Response {
        return $this->success( $this->fetch_all_settings() );
    }

    /**
     * Admin handler: POST / PUT /pcs/v1/admin/settings
     */
    public function update_settings( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $params = $request->get_json_params();
        if ( empty( $params ) || ! is_array( $params ) ) {
            $params = $request->get_params();
        }

        if ( empty( $params ) || ! is_array( $params ) ) {
            return $this->error( 'pcs_invalid_data', 'Date de configurare invalide sau lipsă.', 400 );
        }

        $allowed_keys = array_keys( $this->get_default_settings() );

        foreach ( $params as $key => $value ) {
            $clean_key = sanitize_key( $key );
            if ( ! in_array( $clean_key, $allowed_keys, true ) ) {
                // Still allow custom string keys as long as alphanumeric/underscore
                if ( ! preg_match( '/^[a-z0-9_]{2,64}$/', $clean_key ) ) {
                    continue;
                }
            }

            // Sanitize value depending on key
            if ( is_string( $value ) ) {
                if ( $clean_key === 'contact_email' ) {
                    $clean_value = sanitize_email( $value );
                } elseif ( $clean_key === 'logo_url' ) {
                    $clean_value = esc_url_raw( trim( $value ) );
                } else {
                    $clean_value = sanitize_textarea_field( $value );
                }
            } else {
                $clean_value = is_null( $value ) ? '' : sanitize_text_field( (string) $value );
            }

            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO `{$this->table_name}` (`setting_key`, `setting_value`)
                     VALUES (%s, %s)
                     ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)",
                    $clean_key,
                    $clean_value
                )
            );
        }

        return $this->success( [
            'message'  => 'Setările au fost salvate cu succes.',
            'settings' => $this->fetch_all_settings(),
        ] );
    }

    /**
     * Admin handler: GET /pcs/v1/admin/email/status
     */
    public function get_email_status( WP_REST_Request $request ): WP_REST_Response {
        return $this->success( PCS_Mailer::instance()->get_config_summary() );
    }

    /**
     * Admin handler: POST /pcs/v1/admin/email/test
     */
    public function send_test_email( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_json_params();
        if ( empty( $params ) || ! is_array( $params ) ) {
            $params = $request->get_params();
        }

        $to = sanitize_email( trim( (string) ( $params['to'] ?? '' ) ) );
        if ( empty( $to ) || ! is_email( $to ) ) {
            $to = PCS_Mailer::instance()->get_default_from_email();
        }

        $result = PCS_Mailer::instance()->send_test_email( $to );
        return $this->success( $result );
    }
}

