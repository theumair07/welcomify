<?php
/**
 * Configuration for the WordPress test library.
 *
 * Every value can be overridden with an environment variable, so the same file works
 * locally and in CI. The database is EMPTIED on every run: never point it at a real site.
 *
 * @package Welcomify
 */

// WordPress core to test against. Default: the WordPress install this plugin sits in.
define( 'ABSPATH', rtrim( getenv( 'WP_CORE_DIR' ) ? getenv( 'WP_CORE_DIR' ) : dirname( __DIR__, 4 ), '/\\' ) . '/' );

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ? getenv( 'WP_TESTS_DB_NAME' ) : 'welcomify_tests' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ? getenv( 'WP_TESTS_DB_USER' ) : 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ? getenv( 'WP_TESTS_DB_PASSWORD' ) : '' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ? getenv( 'WP_TESTS_DB_HOST' ) : 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required by the test library.

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Welcomify Tests' );
// The PHP that runs the tests also installs the test site (php may not be on PATH).
define( 'WP_PHP_BINARY', PHP_BINARY );
define( 'WPLANG', '' );
define( 'WP_DEBUG', true );

// Salts for the test site only.
define( 'AUTH_KEY', 'welcomify-tests-auth-key' );
define( 'SECURE_AUTH_KEY', 'welcomify-tests-secure-auth-key' );
define( 'LOGGED_IN_KEY', 'welcomify-tests-logged-in-key' );
define( 'NONCE_KEY', 'welcomify-tests-nonce-key' );
define( 'AUTH_SALT', 'welcomify-tests-auth-salt' );
define( 'SECURE_AUTH_SALT', 'welcomify-tests-secure-auth-salt' );
define( 'LOGGED_IN_SALT', 'welcomify-tests-logged-in-salt' );
define( 'NONCE_SALT', 'welcomify-tests-nonce-salt' );
