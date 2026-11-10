<?php
/**
 * The base configuration for WordPress
 *
 * Copy this file to "wp-config.php" and fill in your local values.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
define( 'DB_NAME', 'pepinieraciumbrudshop' );
define( 'DB_USER', 'pepinieraciumbrudshop' );
define( 'DB_PASSWORD', 'your_db_password_here' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 * Change these to different unique phrases!
 * https://api.wordpress.org/secret-key/1.1/salt/
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );
/**#@-*/

$table_prefix = 'pcs_';

define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

// Pepinieraciumbrud - Headless API Proxy credentials (server-side only, never exposed to browser)
define( 'PCS_WC_CONSUMER_KEY',    'ck_your_consumer_key_here' );
define( 'PCS_WC_CONSUMER_SECRET', 'cs_your_consumer_secret_here' );
define( 'PCS_WC_API_URL',         'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/wc/v3' );
define( 'PCS_JWT_SECRET',         'your_jwt_secret_key_here' );
define( 'PCS_STOREFRONT_ORIGIN',  'http://localhost:3000' );
define( 'PCS_ADMIN_ORIGIN',       'http://localhost:5173' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
