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
        // Always embed the store logo inline (CID) if present in the HTML body so it renders in all email clients
        $this->embed_email_logo( $phpmailer );

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
     * Embed the Pepiniera Ciumbrud logo as an inline CID attachment in PHPMailer
     * so that external email clients (Gmail, Outlook, Apple Mail) always display it
     * even when the server URL is localhost or private.
     */
    private function embed_email_logo( $phpmailer ): void {
        if ( ! is_object( $phpmailer ) || empty( $phpmailer->Body ) || ! is_string( $phpmailer->Body ) ) {
            return;
        }

        $logo_path = $this->get_email_logo_file_path();
        if ( empty( $logo_path ) || ! is_readable( $logo_path ) ) {
            return;
        }

        $cid      = 'pcs-email-logo';
        $logo_url = $this->get_email_logo_url();
        $plugin_logo_url = plugins_url( 'assets/logo-clean.png', PCS_API_PLUGIN_DIR . 'pepinieraciumbrud-api.php' );

        $urls_to_replace = array_filter( array_unique( [
            $logo_url,
            esc_url( $logo_url ),
            $plugin_logo_url,
            esc_url( $plugin_logo_url ),
        ] ) );

        $replaced = false;
        foreach ( $urls_to_replace as $url ) {
            if ( '' !== $url && str_contains( $phpmailer->Body, $url ) ) {
                $phpmailer->Body = str_replace( $url, 'cid:' . $cid, $phpmailer->Body );
                $replaced = true;
            }
        }

        if ( $replaced || str_contains( $phpmailer->Body, 'cid:' . $cid ) ) {
            if ( method_exists( $phpmailer, 'addEmbeddedImage' ) ) {
                $phpmailer->addEmbeddedImage( $logo_path, $cid, 'logo-pepiniera-ciumbrud.png', 'base64', 'image/png' );
            }
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
     * Get the store contact phone number from settings.
     */
    public function get_store_phone(): string {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'settings';
        $contact_phone  = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'contact_phone' )
        );

        if ( ! empty( $contact_phone ) && is_string( $contact_phone ) ) {
            return trim( $contact_phone );
        }

        return '+40 765 152 832';
    }

    /**
     * Get the store physical address from settings.
     */
    public function get_store_address(): string {
        global $wpdb;
        $settings_table  = $wpdb->prefix . 'settings';
        $contact_address = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'contact_address' )
        );

        if ( ! empty( $contact_address ) && is_string( $contact_address ) ) {
            return trim( $contact_address );
        }

        return 'Strada Vasile Lucaci 77, Sat Ciumbrud, Jud. Alba, România';
    }

    /**
     * Get the brand slogan used across the storefront and emails.
     */
    public function get_store_slogan(): string {
        return 'Din 2000 · Satul Rozelor';
    }

    /**
     * Get the secondary brand motto/tagline.
     */
    public function get_store_tagline(): string {
        return 'Puieți care prind rădăcini, și rodesc ani la rând';
    }

    /**
     * Get the public storefront URL.
     */
    public function get_storefront_url(): string {
        $url = home_url();
        if ( defined( 'PCS_STOREFRONT_ORIGIN' ) && ! empty( PCS_STOREFRONT_ORIGIN ) ) {
            $url = (string) PCS_STOREFRONT_ORIGIN;
        } elseif ( getenv( 'PCS_STOREFRONT_ORIGIN' ) ) {
            $url = (string) getenv( 'PCS_STOREFRONT_ORIGIN' );
        }
        return apply_filters( 'pcs_mailer_storefront_url', rtrim( $url, '/' ) );
    }

    /**
     * Get the admin dashboard URL.
     */
    public function get_admin_url(): string {
        $url = admin_url();
        if ( defined( 'PCS_ADMIN_ORIGIN' ) && ! empty( PCS_ADMIN_ORIGIN ) ) {
            $url = (string) PCS_ADMIN_ORIGIN;
        } elseif ( getenv( 'PCS_ADMIN_ORIGIN' ) ) {
            $url = (string) getenv( 'PCS_ADMIN_ORIGIN' );
        }
        return apply_filters( 'pcs_mailer_admin_url', rtrim( $url, '/' ) );
    }

    /**
     * Resolve the store logo URL for email headers.
     */
    public function get_email_logo_url(): string {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'settings';
        $logo_url       = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM `{$settings_table}` WHERE setting_key = %s LIMIT 1", 'logo_url' )
        );

        if ( ! empty( $logo_url ) && is_string( $logo_url ) ) {
            return trim( $logo_url );
        }

        $wc_header_img = get_option( 'woocommerce_email_header_image' );
        if ( ! empty( $wc_header_img ) && is_string( $wc_header_img ) ) {
            return trim( $wc_header_img );
        }

        return plugins_url( 'assets/logo-clean.png', PCS_API_PLUGIN_DIR . 'pepinieraciumbrud-api.php' );
    }

    /**
     * Resolve the local filesystem path for the store logo so PHPMailer can embed it via CID.
     */
    public function get_email_logo_file_path(): string {
        $logo_url = $this->get_email_logo_url();

        if ( ! empty( $logo_url ) ) {
            $uploads = wp_get_upload_dir();
            if ( ! empty( $uploads['baseurl'] ) && ! empty( $uploads['basedir'] ) && str_starts_with( $logo_url, $uploads['baseurl'] ) ) {
                $relative = ltrim( substr( $logo_url, strlen( $uploads['baseurl'] ) ), '/' );
                $candidate = trailingslashit( $uploads['basedir'] ) . $relative;
                if ( is_readable( $candidate ) ) {
                    return $candidate;
                }
            }
        }

        $fallback = PCS_API_PLUGIN_DIR . 'assets/logo-clean.png';
        return is_readable( $fallback ) ? $fallback : '';
    }

    /**
     * Render a complete standalone HTML email wrapped in the Pepiniera Ciumbrud
     * red & warm cream theme (Logo, Title, Slogan, Red Heading Banner, Body, Footer).
     *
     * @param array{
     *   subject?: string,
     *   heading?: string,
     *   eyebrow?: string,
     *   body_html?: string,
     *   footer_note?: string
     * } $args
     */
    public function render_branded_email( array $args ): string {
        $store_name     = esc_html( $this->get_default_from_name() );
        $slogan         = esc_html( $this->get_store_slogan() );
        $tagline        = esc_html( $this->get_store_tagline() );
        $phone          = esc_html( $this->get_store_phone() );
        $phone_href     = esc_attr( preg_replace( '/\s+/', '', $this->get_store_phone() ) );
        $email_addr     = esc_html( $this->get_default_from_email() );
        $address        = esc_html( $this->get_store_address() );
        $storefront_url = esc_url( $this->get_storefront_url() );
        $logo_url       = esc_url( $this->get_email_logo_url() );
        $year           = gmdate( 'Y' );

        $subject     = esc_html( (string) ( $args['subject'] ?? $store_name ) );
        $heading     = esc_html( (string) ( $args['heading'] ?? $store_name ) );
        $eyebrow     = esc_html( (string) ( $args['eyebrow'] ?? '' ) );
        $body_html   = (string) ( $args['body_html'] ?? '' );
        $footer_note = (string) ( $args['footer_note'] ?? '' );

        $eyebrow_html = '';
        if ( '' !== $eyebrow ) {
            $eyebrow_html = "<div style=\"font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2.2px; color: #F2BDBD; font-weight: 600; margin-bottom: 6px;\">{$eyebrow}</div>";
        }

        $footer_note_html = '';
        if ( '' !== $footer_note ) {
            $footer_note_html = "<div style=\"background-color: #FCF2F2; border-top: 1px solid #F2BDBD; padding: 16px 28px; text-align: center; font-size: 12px; color: #7D0C0C; line-height: 1.5;\">{$footer_note}</div>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="ro-RO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF6EE; color: #2E2C2A; margin: 0; padding: 28px 12px; line-height: 1.65; -webkit-text-size-adjust: 100%;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #FAF6EE;">
        <tr>
            <td align="center" valign="top">
                <div style="max-width: 620px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2DACB; overflow: hidden; box-shadow: 0 12px 32px -12px rgba(178, 17, 17, 0.14); text-align: left;">
                    <!-- Top Announcement Strip -->
                    <div style="background-color: #7D0C0C; color: #FAF6EE; padding: 8px 24px; font-size: 11px; border-bottom: 1px solid rgba(255, 255, 255, 0.12);">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="left" valign="middle" style="color: #FAF6EE; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px;">
                                    <span style="color: #F2BDBD;">&#9679;</span> Livrare în toată țara &middot; Ambalare specială pentru rădăcini
                                </td>
                                <td align="right" valign="middle" style="font-size: 11px; font-weight: 700; white-space: nowrap; padding-left: 10px;">
                                    <a href="tel:{$phone_href}" style="color: #FFFFFF; text-decoration: none;">&#9742; {$phone}</a>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Brand Identity Header (Logo + Title + Slogan) -->
                    <div style="background-color: #FAF6EE; padding: 20px 28px; border-bottom: 1px solid #E2DACB;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td width="68" valign="middle" style="padding-right: 16px; width: 68px;">
                                    <a href="{$storefront_url}" target="_blank" style="display: inline-block; text-decoration: none;">
                                        <img src="{$logo_url}" alt="{$store_name}" width="64" height="64" style="width: 64px; height: 64px; max-width: 64px; object-fit: contain; display: block; border: 0; margin: 0;" />
                                    </a>
                                </td>
                                <td valign="middle" style="border-left: 2px solid rgba(178, 17, 17, 0.22); padding-left: 16px;">
                                    <div style="font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 25px; font-weight: 700; color: #7D0C0C; line-height: 1.1; letter-spacing: 0.3px;">
                                        <a href="{$storefront_url}" target="_blank" style="color: #7D0C0C; text-decoration: none;">{$store_name}</a>
                                    </div>
                                    <div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2.4px; color: #B21111; font-weight: 700; margin-top: 5px;">
                                        {$slogan}
                                    </div>
                                    <div style="font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 12px; color: #5F5A54; margin-top: 3px;">
                                        {$tagline}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Red Theme Banner -->
                    <div style="background-color: #B21111; background-image: linear-gradient(135deg, #B21111 0%, #7D0C0C 100%); color: #FFFFFF; padding: 22px 28px;">
                        {$eyebrow_html}
                        <h1 style="margin: 0; font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 24px; font-weight: 700; color: #FFFFFF; line-height: 1.25; letter-spacing: 0.2px;">
                            {$heading}
                        </h1>
                    </div>

                    <!-- Main Email Content -->
                    <div style="padding: 28px; background-color: #FFFFFF; color: #2E2C2A; font-size: 15px; line-height: 1.65;">
                        {$body_html}
                    </div>

                    {$footer_note_html}

                    <!-- Branded Footer -->
                    <div style="background-color: #7D0C0C; color: #FAF6EE; padding: 24px 28px; text-align: center; border-top: 3px solid #B21111;">
                        <div style="font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #FFFFFF; letter-spacing: 0.4px;">
                            {$store_name}
                        </div>
                        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 2.2px; color: #F2BDBD; font-weight: 600; margin: 3px 0 12px;">
                            {$slogan}
                        </div>
                        <div style="font-size: 12px; color: #FAF6EE; margin-bottom: 6px;">
                            <a href="tel:{$phone_href}" style="color: #FFFFFF; text-decoration: none; font-weight: 600;">{$phone}</a>
                            &nbsp;&middot;&nbsp;
                            <a href="mailto:{$email_addr}" style="color: #FFFFFF; text-decoration: underline;">{$email_addr}</a>
                        </div>
                        <div style="font-size: 11px; color: #F2BDBD; margin-bottom: 10px;">
                            {$address}
                        </div>
                        <div style="font-size: 11px; color: rgba(250, 246, 238, 0.7); border-top: 1px solid rgba(255, 255, 255, 0.14); padding-top: 10px;">
                            &copy; {$year} {$store_name}. Toate drepturile rezervate.
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
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

        $body_html = "<p style=\"margin-top: 0;\">Acest email confirmă că serviciul SMTP pentru <strong>" . esc_html( $site_title ) . "</strong> este configurat și funcțional.</p>"
                   . "<div style=\"background-color: #FAF6EE; border: 1px solid #E2DACB; border-left: 4px solid #B21111; border-radius: 10px; padding: 16px 20px; margin: 20px 0;\">"
                   . "<div style=\"font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #7D0C0C; margin-bottom: 10px;\">Detalii Configurare SMTP</div>"
                   . "<table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" style=\"font-size: 14px; color: #2E2C2A;\">"
                   . "<tr><td style=\"color: #5F5A54; width: 130px;\"><strong>Data testului:</strong></td><td>" . esc_html( $date_str ) . "</td></tr>"
                   . "<tr><td style=\"color: #5F5A54;\"><strong>Server SMTP:</strong></td><td>" . esc_html( $config['host'] ?: 'Nespecificat' ) . "</td></tr>"
                   . "<tr><td style=\"color: #5F5A54;\"><strong>Port:</strong></td><td>" . esc_html( (string) $config['port'] ) . "</td></tr>"
                   . "<tr><td style=\"color: #5F5A54;\"><strong>Securitate:</strong></td><td>" . esc_html( $config['secure'] ?: 'Fără criptare' ) . "</td></tr>"
                   . "<tr><td style=\"color: #5F5A54;\"><strong>Expeditor:</strong></td><td>" . esc_html( $config['from'] ) . "</td></tr>"
                   . "</table>"
                   . "</div>";

        $html = $this->render_branded_email( [
            'subject'   => $subject,
            'eyebrow'   => 'VERIFICARE SISTEM · SMTP',
            'heading'   => 'Test expediere email — ' . $site_title,
            'body_html' => $body_html,
        ] );

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

