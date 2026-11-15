<?php
/**
 * Automated PHP Syntax Linter for Pepiniera Ciumbrud V2 Backend.
 * Checks all custom plugin files and database migrations/seeds.
 */

$base_dir = dirname( __DIR__ );
$scan_dirs = [
    $base_dir . '/wp-content/plugins/pepinieraciumbrud-api',
    $base_dir . '/database',
    $base_dir . '/tests',
];

$files = [];
foreach ( $scan_dirs as $dir ) {
    if ( ! is_dir( $dir ) ) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS )
    );
    foreach ( $iterator as $file ) {
        if ( $file->isFile() && strtolower( $file->getExtension() ) === 'php' ) {
            $files[] = $file->getPathname();
        }
    }
}

sort( $files );

echo "🔍 Running PHP syntax checks on " . count( $files ) . " files...\n";

$failures = 0;
foreach ( $files as $file ) {
    $rel_path = str_replace( $base_dir . '/', '', $file );
    $output   = [];
    $ret      = 0;
    exec( sprintf( 'php -l %s 2>&1', escapeshellarg( $file ) ), $output, $ret );
    if ( $ret !== 0 ) {
        $failures++;
        echo "  ✘ Syntax error in {$rel_path}:\n    " . implode( "\n    ", $output ) . "\n";
    }
}

if ( $failures > 0 ) {
    echo "\n❌ PHP lint failed with {$failures} error(s).\n";
    exit( 1 );
}

echo "✔ All " . count( $files ) . " PHP files passed syntax check.\n";
exit( 0 );
