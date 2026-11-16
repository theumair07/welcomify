<?php
/**
 * PHPUnit bootstrap: loads the WordPress test library and the plugin.
 *
 * Run with: composer install && composer test
 *
 * @package Welcomify
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$umy_wdw_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $umy_wdw_tests_dir ) {
	// Shipped by the wp-phpunit/wp-phpunit Composer package.
	$umy_wdw_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! file_exists( $umy_wdw_tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test library not found in {$umy_wdw_tests_dir}. Run composer install." . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output.
	exit( 1 );
}

// Tell the test library where the Yoast PHPUnit Polyfills are.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );
}

require_once $umy_wdw_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__ ) . '/welcomify.php';
	}
);

require $umy_wdw_tests_dir . '/includes/bootstrap.php';
