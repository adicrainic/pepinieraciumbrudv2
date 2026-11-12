<?php
/**
 * SMTP Mailer & Email Dispatch configuration.
 * Configures PHPMailer to route all WordPress and WooCommerce emails through SMTP
 * based on constants defined in wp-config.php or environment variables.
 * Also provides an admin endpoint to inspect SMTP status and test email delivery.
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Mailer {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Hook PHPMailer configuration on every email dispatch
        add_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ], 10, 1 );

        // Ensure clean sender email and name across all emails
        add_filter( 'wp_mail_from', [ $this, 'filter_from_email' ], 20, 1 );
        add_filter( 'wp_mail_from_name', [ $this, 'filter_from_name' ], 20, 1 );
    }

    /**
     * Check whether SMTP is enabled in the configuration.
     */
    public function is_smtp_enabled(): bool {
        if ( defined( 'PCS_SMTP_ENABLED' ) ) {
            return (bool) PCS_SMTP_ENABLED;
        }
        return defined( 'PCS_SMTP_HOST' ) && ! empty( PCS_SMTP_HOST );
    }

    /**
     * Get current SMTP configuration summary (passwords hidden).
     */
    public function get_config_summary(): array {
        $enabled = $this->is_smtp_enabled();
        $host    = defined( 'PCS_SMTP_HOST' ) ? (string) PCS_SMTP_HOST : '';
        $port    = defined( 'PCS_SMTP_PORT' ) ? (int) PCS_SMTP_PORT : 587;
        $auth    = defined( 'PCS_SMTP_AUTH' ) ? (bool) PCS_SMTP_AUTH : true;
        $user    = defined( 'PCS_SMTP_USER' ) ? (string) PCS_SMTP_USER : '';
        $secure  = defined( 'PCS_SMTP_SECURE' ) ? (string) PCS_SMTP_SECURE : 'tls';

        return [
            'enabled'   => $enabled,
            'host'      => $host,
            'port'      => $port,
            'auth'      => $auth,
            'user'      => $user,
            'secure'    => $secure,
            'from'      => $this->get_default_from_email(),
            'from_name' => $this->get_default_from_name(),
        ];
    }

    /**
     * Configure PHPMailer instance before sending any email.
     */
    public function configure_phpmailer( $phpmailer ): void {
        if ( ! $this->is_smtp_enabled() ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = (string) PCS_SMTP_HOST;
        $phpmailer->Port = defined( 'PCS_SMTP_PORT' ) ? (int) PCS_SMTP_PORT : 587;

        $auth = defined( 'PCS_SMTP_AUTH' ) ? (bool) PCS_SMTP_AUTH : true;
        $phpmailer->SMTPAuth = $auth;
        if ( $auth ) {
            $user = defined( 'PCS_SMTP_USER' ) ? trim( (string) PCS_SMTP_USER ) : '';
            $pass = defined( 'PCS_SMTP_PASS' ) ? trim( (string) PCS_SMTP_PASS ) : '';

            // If using Gmail SMTP and password has spaces (Google App Passwords format: "xxxx xxxx xxxx xxxx"), strip spaces
            if ( str_contains( (string) PCS_SMTP_HOST, 'gmail.com' ) ) {
                $pass = str_replace( ' ', '', $pass );
            }

            $phpmailer->Username = $user;
            $phpmailer->Password = $pass;
        }

        // Encryption: 'ssl', 'tls', or '' / 'none' (for local Mailpit / MailHog)
        $secure = defined( 'PCS_SMTP_SECURE' ) ? strtolower( (string) PCS_SMTP_SECURE ) : 'tls';
        if ( in_array( $secure, [ '', 'none', 'false', '0' ], true ) ) {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        } else {
            $phpmailer->SMTPSecure = $secure;
            $phpmailer->SMTPAutoTLS = true;
        }

        $phpmailer->Timeout = defined( 'PCS_SMTP_TIMEOUT' ) ? (int) PCS_SMTP_TIMEOUT : 15;

        // Ensure from is set if PHPMailer has an invalid default (e.g. wordpress@localhost)
        if ( empty( $phpmailer->From ) || str_contains( $phpmailer->From, 'localhost' ) ) {
            $phpmailer->From     = $this->get_default_from_email();
            $phpmailer->FromName = $this->get_default_from_name();
        }
    }

    /**
     * Filter default From email address.
     */
    public function filter_from_email( string $from_email ): string {
        if ( ! empty( $from_email ) && is_email( $from_email ) && ! str_contains( $from_email, 'localhost' ) ) {
            if ( defined( 'PCS_SMTP_FROM' ) && is_email( PCS_SMTP_FROM ) && str_contains( $from_email, 'wordpress@' ) ) {
                return PCS_SMTP_FROM;
            }
            return $from_email;
        }

        return $this->get_default_from_email();
    }

    /**
     * Filter default From name.
     */
    public function filter_from_name( string $from_name ): string {
        if ( ! empty( $from_name ) && $from_name !== 'WordPress' ) {
            return $from_name;
        }
        return $this->get_default_from_name();
    }

    public function get_default_from_email(): string {
        if ( defined( 'PCS_SMTP_FROM' ) && is_email( PCS_SMTP_FROM ) ) {
            return PCS_SMTP_FROM;
        }

        global $wpdb;
        $settings_table = $wpdb->prefix . 'settings';
        $contact_email  = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'contact_email' )
        );

        if ( ! empty( $contact_email ) && is_email( $contact_email ) ) {
            return $contact_email;
        }

        $admin_email = get_option( 'admin_email' );
        if ( ! empty( $admin_email ) && is_email( $admin_email ) && ! str_contains( $admin_email, 'localhost' ) ) {
            return $admin_email;
        }

        return 'contact@pepinieraciumbrud.ro';
    }

    public function get_default_from_name(): string {
        if ( defined( 'PCS_SMTP_FROM_NAME' ) && ! empty( PCS_SMTP_FROM_NAME ) ) {
            return (string) PCS_SMTP_FROM_NAME;
        }

        $site_title = get_bloginfo( 'name' );
        if ( ! empty( $site_title ) && $site_title !== 'WordPress' ) {
            return $site_title;
        }

        return 'Pepiniera Ciumbrud';
    }

    /**
     * Send a test email and return array with status and debug details.
     */
    public function send_test_email( string $recipient ): array {
        $last_error = '';
        $error_capture = function( $wp_error ) use ( &$last_error ) {
            if ( is_wp_error( $wp_error ) ) {
                $last_error = $wp_error->get_error_message();
            }
        };

        add_action( 'wp_mail_failed', $error_capture );

        $site_title = $this->get_default_from_name();
        $subject    = sprintf( '[%s] Test expediere email (SMTP)', $site_title );
        $date_str   = date_i18n( 'j F Y, H:i:s', current_time( 'timestamp' ) );
        $config     = $this->get_config_summary();

        $html = "<h2>Test expediere email &mdash; {$site_title}</h2>"
              . "<p>Acest email confirmă că serviciul SMTP este configurat și funcțional.</p>"
              . "<ul>"
              . "<li><strong>Data:</strong> {$date_str}</li>"
              . "<li><strong>Server SMTP:</strong> " . esc_html( $config['host'] ?: 'Nespecificat' ) . "</li>"
              . "<li><strong>Port:</strong> " . esc_html( (string) $config['port'] ) . "</li>"
              . "<li><strong>Securitate:</strong> " . esc_html( $config['secure'] ?: 'Fără criptare' ) . "</li>"
              . "<li><strong>Expeditor:</strong> " . esc_html( $config['from'] ) . "</li>"
              . "</ul>";

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

        $sent = (bool) wp_mail( $recipient, $subject, $html, $headers );

        remove_action( 'wp_mail_failed', $error_capture );

        return [
            'success' => $sent,
            'message' => $sent ? "Emailul de test a fost expediat cu succes către {$recipient}." : ( $last_error ?: 'Expedierea a eșuat.' ),
            'error'   => $sent ? null : $last_error,
            'config'  => $config,
        ];
    }
}

