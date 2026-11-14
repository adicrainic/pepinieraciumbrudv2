<?php
/**
 * Phinx Database Migration Configuration
 * Pepiniera Ciumbrud V2
 *
 * Automatically pulls DB credentials from wp-config.php so no credentials
 * need to be duplicated between WordPress and Phinx.
 */

$wp_config = dirname(__DIR__) . '/wp-config.php';
if (file_exists($wp_config)) {
    require_once $wp_config;
} else {
    throw new RuntimeException("Error: wp-config.php not found at {$wp_config}");
}

$db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
$db_port = 3306;

// Handle host:port syntax if present
if (strpos($db_host, ':') !== false) {
    [$db_host, $db_port] = explode(':', $db_host, 2);
}

// Get WordPress table prefix (defaults to 'pcs_')
$prefix = isset($table_prefix) ? $table_prefix : 'pcs_';

return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
        'seeds'      => __DIR__ . '/seeds',
    ],
    'environments' => [
        'default_migration_table' => $prefix . 'phinxlog',
        'default_environment'     => 'production',
        'production' => [
            'adapter'   => 'mysql',
            'host'      => $db_host,
            'name'      => defined('DB_NAME') ? DB_NAME : 'pepinieraciumbrudshop',
            'user'      => defined('DB_USER') ? DB_USER : 'pepinieraciumbrudshop',
            'pass'      => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            'port'      => (int) $db_port,
            'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];

