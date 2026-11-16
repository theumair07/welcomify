<?php
/**
 * Signed token that lets the dashboard iframe load the chosen template for any role.
 *
 * @package Welcomify
 */

namespace UMY_WDW_Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lets the dashboard iframe load a page-builder template for any user role.
 *
 * Bricks and Elementor redirect visitors who are not allowed to use their
 * builder away from template URLs (to the front page). The Welcomify welcome
 * panel loads the template in an iframe, so roles without builder access
 * (for example custom roles) saw the front page instead of the template.
 *
 * The iframe URL now carries a short-lived signed token that is bound to the
 * template ID and to the logged-in user. Only a request with a valid token is
 * allowed through. Nothing is changed in the page builders' settings and
 * everyone else is still redirected as before.
 *
 * @since 1.5.0
 */
final class Embed_Access {

	/**
	 * Token lifetime in seconds.
	 *
	 * @since 1.5.0
	 */
	const TTL = 300;

	/**
	 * Query argument holding the template ID.
	 *
	 * @since 1.5.0
	 */
	const ARG_TEMPLATE = 'umy_wdw_tid';

	/**
	 * Query argument holding the signed token.
	 *
	 * @since 1.5.0
	 */
	const ARG_TOKEN = 'umy_wdw_token';

	/**
	 * Cached result of the token check for the current request.
	 *
	 * @since 1.5.0
	 * @var int|null
	 */
	private static $verified_template_id = null;

	/**
	 * Whether WordPress is running the main query of the current request right now.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private static $in_main_query = false;

	/**
	 * Registers the hooks. Must run early, before the page builders read their settings.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public static function init() {
		// Bricks: skips its "redirect users without builder access" rule when "publicTemplates" is set.
		add_filter( 'option_bricks_global_settings', array( __CLASS__, 'allow_bricks_template' ) );

		// Private pages: WordPress answers 404 to users without the read_private_pages capability.
		add_filter( 'user_has_cap', array( __CLASS__, 'allow_private_read' ), 10, 2 );

		// The private read access above is limited to the main query (the page itself), not other queries.
		add_action( 'pre_get_posts', array( __CLASS__, 'main_query_start' ) );
		add_filter( 'the_posts', array( __CLASS__, 'main_query_end' ), PHP_INT_MAX, 2 );

		// Runs before Bricks (10) and Elementor (10) check access on template_redirect.
		add_action( 'template_redirect', array( __CLASS__, 'handle_template_request' ), 1 );
	}

	/**
	 * Lets a role without read_private_pages see the private page chosen for it, in the dashboard iframe.
	 *
	 * Read access only. Only while a valid token is present, only for a normal front-end page view
	 * (never in admin, AJAX, REST or on a form post), and only while the main query runs. It is also
	 * switched off for good at template_redirect (see handle_template_request()), so nothing else on
	 * the page is affected.
	 *
	 * @since 1.5.0
	 * @param array $allcaps All the user's capabilities.
	 * @param array $caps    Required primitive capabilities.
	 * @return array
	 */
	public static function allow_private_read( $allcaps, $caps ) {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $allcaps;
		}

		if ( ! self::$in_main_query ) {
			return $allcaps;
		}

		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) {
			return $allcaps;
		}

		if ( ! self::get_verified_template_id() ) {
			return $allcaps;
		}

		foreach ( (array) $caps as $cap ) {
			if ( is_string( $cap ) && 0 === strpos( $cap, 'read_private_' ) ) {
				$allcaps[ $cap ] = true;
			}
		}

		return $allcaps;
	}

	/**
	 * Marks the start of the main query.
	 *
	 * @since 1.5.0
	 * @param \WP_Query $query The query being prepared.
	 * @return void
	 */
	public static function main_query_start( $query ) {
		if ( $query instanceof \WP_Query && $query->is_main_query() ) {
			self::$in_main_query = true;
		}
	}

	/**
	 * Marks the end of the main query. A filter, so it returns the posts unchanged.
	 *
	 * @since 1.5.0
	 * @param array     $posts The posts found.
	 * @param \WP_Query $query The query.
	 * @return array
	 */
	public static function main_query_end( $posts, $query ) {
		if ( $query instanceof \WP_Query && $query->is_main_query() ) {
			self::$in_main_query = false;
		}

		return $posts;
	}

	/**
	 * Adds the signed token to an iframe URL.
	 *
	 * @since 1.5.0
	 * @param string $url         Template URL.
	 * @param int    $template_id Template post ID.
	 * @return string
	 */
	public static function add_token( $url, $template_id ) {
		$template_id = absint( $template_id );
		$user_id     = get_current_user_id();

		if ( ! $template_id || ! $user_id ) {
			return $url;
		}

		$expires = time() + self::TTL;

		return add_query_arg(
			array(
				self::ARG_TEMPLATE => $template_id,
				self::ARG_TOKEN    => $expires . '.' . self::sign( $template_id, $user_id, $expires ),
			),
			$url
		);
	}

	/**
	 * Bricks: treat templates as public for a valid dashboard iframe request only.
	 *
	 * @since 1.5.0
	 * @param mixed $settings Bricks global settings.
	 * @return mixed
	 */
	public static function allow_bricks_template( $settings ) {
		if ( self::get_verified_template_id() ) {
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}
			$settings['publicTemplates'] = true;
		}

		return $settings;
	}

	/**
	 * Keeps the access limited to the template the token was issued for and
	 * lifts Elementor's template redirect for that one template.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public static function handle_template_request() {
		$template_id = self::get_verified_template_id();

		if ( ! $template_id ) {
			return;
		}

		// The main query is done: switch the private-page read access off again.
		remove_filter( 'user_has_cap', array( __CLASS__, 'allow_private_read' ), 10 );

		// The token is only for viewing that one page or template. Anything else (another page,
		// a search, an archive or a feed) is sent away before any content is printed.
		if ( is_feed() || ! is_singular() || get_queried_object_id() !== $template_id ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( ! is_singular( array( 'bricks_template', 'elementor_library' ) ) ) {
			return;
		}

		// Elementor: remove its redirect for this request only.
		if ( is_singular( 'elementor_library' ) && class_exists( '\Elementor\Plugin' ) ) {
			$plugin = \Elementor\Plugin::$instance;
			if ( $plugin && isset( $plugin->templates_manager ) ) {
				$source = $plugin->templates_manager->get_source( 'local' );
				if ( $source ) {
					remove_action( 'template_redirect', array( $source, 'block_template_frontend' ) );
				}
			}
		}
	}

	/**
	 * Returns the template ID if the current request carries a valid token, otherwise 0.
	 *
	 * @since 1.5.0
	 * @return int
	 */
	private static function get_verified_template_id() {
		if ( null !== self::$verified_template_id ) {
			return self::$verified_template_id;
		}

		self::$verified_template_id = 0;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified below with a signed token.
		if ( ! isset( $_GET[ self::ARG_TEMPLATE ], $_GET[ self::ARG_TOKEN ] ) ) {
			return 0;
		}

		$template_id = absint( $_GET[ self::ARG_TEMPLATE ] );
		$token       = sanitize_text_field( wp_unslash( $_GET[ self::ARG_TOKEN ] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$user_id = get_current_user_id();
		$parts   = explode( '.', $token );

		if ( ! $template_id || ! $user_id || 2 !== count( $parts ) ) {
			return 0;
		}

		$expires = absint( $parts[0] );

		if ( $expires < time() || $expires > time() + self::TTL ) {
			return 0;
		}

		if ( ! hash_equals( self::sign( $template_id, $user_id, $expires ), $parts[1] ) ) {
			return 0;
		}

		self::$verified_template_id = $template_id;

		return $template_id;
	}

	/**
	 * Signs the template ID, user ID and expiry time.
	 *
	 * @since 1.5.0
	 * @param int $template_id Template post ID.
	 * @param int $user_id     User ID.
	 * @param int $expires     Expiry timestamp.
	 * @return string
	 */
	private static function sign( $template_id, $user_id, $expires ) {
		return hash_hmac( 'sha256', $template_id . '|' . $user_id . '|' . $expires, wp_salt( 'auth' ) );
	}
}
