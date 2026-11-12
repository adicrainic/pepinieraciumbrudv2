<?php
/**
 * Quick CLI tool to test email dispatch via WordPress wp_mail() & PCS_Mailer.
 * Usage: php test-email.php [recipient@example.com]
 */

if ( php_sapi_name() !== 'cli' ) {
    die( "CLI only.\n" );
}

require_once __DIR__ . '/wp-load.php';

$recipient = $argv[1] ?? null;

if ( empty( $recipient ) ) {
    $recipient = defined( 'PCS_SMTP_FROM' ) ? PCS_SMTP_FROM : get_option( 'admin_email' );
}

echo "=== Pepiniera Ciumbrud - Email Diagnostic Tool ===\n";
$config = PCS_Mailer::instance()->get_config_summary();

echo "SMTP Enabled:   " . ( $config['enabled'] ? 'YES' : 'NO (using local mail fallback)' ) . "\n";
echo "Host:           " . ( $config['host'] ?: '(none)' ) . "\n";
echo "Port:           " . $config['port'] . "\n";
echo "Auth:           " . ( $config['auth'] ? 'YES' : 'NO' ) . "\n";
echo "User:           " . ( $config['user'] ?: '(none)' ) . "\n";
echo "Secure:         " . ( $config['secure'] ?: 'none' ) . "\n";
echo "From Email:     " . $config['from'] . "\n";
echo "From Name:      " . $config['from_name'] . "\n";
echo "Recipient:      " . $recipient . "\n";
echo "---------------------------------------------------\n";
echo "Attempting to send test email...\n";

$result = PCS_Mailer::instance()->send_test_email( $recipient );

if ( $result['success'] ) {
    echo "SUCCESS: " . $result['message'] . "\n";
    exit( 0 );
} else {
    echo "FAILED: " . $result['message'] . "\n";
    if ( ! empty( $result['error'] ) ) {
        echo "Error details: " . $result['error'] . "\n";
    }
    exit( 1 );
}

