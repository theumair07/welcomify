<?php
/**
 * Tests for the dashboard iframe token (Embed_Access).
 *
 * @package Welcomify
 */

use UMY_WDW_Plugin\Embed_Access;

/**
 * Thrown instead of a real redirect, so a test can see where a request was sent.
 */
class UMY_WDW_Test_Redirect extends Exception {}

/**
 * Embed_Access tests.
 */
class Test_Embed_Access extends WP_UnitTestCase {

	/**
	 * User with a custom role that only has "read".
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Private page used as the dashboard template.
	 *
	 * @var int
	 */
	private $template_id;

	/**
	 * Administrator who owns the private content.
	 *
	 * @var int
	 */
	private $admin_id;

	public function set_up() {
		parent::set_up();

		add_role( 'umy_test_viewer', 'Viewer', array( 'read' => true ) );
		$this->user_id     = self::factory()->user->create( array( 'role' => 'umy_test_viewer' ) );
		$this->admin_id    = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->template_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
				'post_author' => $this->admin_id,
			)
		);

		wp_set_current_user( $this->user_id );
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->reset_state();
	}

	public function tear_down() {
		remove_role( 'umy_test_viewer' );
		$_GET = array();
		$this->reset_state();
		parent::tear_down();
	}

	/**
	 * Clears the per-request state kept by Embed_Access.
	 */
	private function reset_state() {
		$this->set_static( 'verified_template_id', null );
		$this->set_static( 'in_main_query', false );
	}

	private function set_static( $name, $value ) {
		$prop = new ReflectionProperty( Embed_Access::class, $name );
		$prop->setAccessible( true );
		$prop->setValue( null, $value );
	}

	private function verified_id() {
		$method = new ReflectionMethod( Embed_Access::class, 'get_verified_template_id' );
		$method->setAccessible( true );
		return $method->invoke( null );
	}

	/**
	 * Puts the query string of a URL into $_GET, like a real request.
	 */
	private function use_url( $url ) {
		$query = (string) wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $query, $args );
		$_GET = $args;
		$this->reset_state();
	}

	private function token_url( $url, $template_id = null ) {
		return Embed_Access::add_token( $url, null === $template_id ? $this->template_id : $template_id );
	}

	/**
	 * Runs handle_template_request() and returns the redirect target, or '' when there was none.
	 */
	private function redirect_target() {
		$catch = static function ( $location ) {
			throw new UMY_WDW_Test_Redirect( $location );
		};
		add_filter( 'wp_redirect', $catch );

		try {
			Embed_Access::handle_template_request();
			return '';
		} catch ( UMY_WDW_Test_Redirect $redirect ) {
			return $redirect->getMessage();
		} finally {
			remove_filter( 'wp_redirect', $catch );
		}
	}

	/*
	 * Token checks.
	 */

	public function test_valid_token_is_accepted() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->assertSame( $this->template_id, $this->verified_id() );
	}

	public function test_token_is_bound_to_the_user() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'umy_test_viewer' ) ) );
		$this->reset_state();

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_token_needs_a_logged_in_user() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		wp_set_current_user( 0 );
		$this->reset_state();

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_token_is_bound_to_the_template() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$_GET[ Embed_Access::ARG_TEMPLATE ] = (string) ( $this->template_id + 1 );
		$this->set_static( 'verified_template_id', null );

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_expired_token_is_rejected() {
		$expired = time() - 1;
		$sign    = new ReflectionMethod( Embed_Access::class, 'sign' );
		$sign->setAccessible( true );

		$_GET = array(
			Embed_Access::ARG_TEMPLATE => (string) $this->template_id,
			Embed_Access::ARG_TOKEN    => $expired . '.' . $sign->invoke( null, $this->template_id, $this->user_id, $expired ),
		);

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_token_valid_too_far_in_the_future_is_rejected() {
		$future = time() + Embed_Access::TTL + 60;
		$sign   = new ReflectionMethod( Embed_Access::class, 'sign' );
		$sign->setAccessible( true );

		$_GET = array(
			Embed_Access::ARG_TEMPLATE => (string) $this->template_id,
			Embed_Access::ARG_TOKEN    => $future . '.' . $sign->invoke( null, $this->template_id, $this->user_id, $future ),
		);

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_malformed_token_is_rejected() {
		$_GET = array(
			Embed_Access::ARG_TEMPLATE => (string) $this->template_id,
			Embed_Access::ARG_TOKEN    => 'not-a-token',
		);

		$this->assertSame( 0, $this->verified_id() );
	}

	public function test_no_token_for_visitors() {
		wp_set_current_user( 0 );
		$url = home_url( '/' );

		$this->assertSame( $url, Embed_Access::add_token( $url, $this->template_id ) );
	}

	/*
	 * Private read access.
	 */

	public function test_private_read_is_granted_during_the_main_query_with_a_token() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->set_static( 'in_main_query', true );

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'read_private_pages' ) );

		$this->assertTrue( $caps['read_private_pages'] );
	}

	public function test_private_read_is_not_granted_outside_the_main_query() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->set_static( 'in_main_query', false );

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'read_private_pages' ) );

		$this->assertArrayNotHasKey( 'read_private_pages', $caps );
	}

	public function test_private_read_is_not_granted_without_a_token() {
		$this->set_static( 'in_main_query', true );

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'read_private_pages' ) );

		$this->assertArrayNotHasKey( 'read_private_pages', $caps );
	}

	public function test_private_read_is_not_granted_on_a_post_request() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->set_static( 'in_main_query', true );
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'read_private_pages' ) );

		$this->assertArrayNotHasKey( 'read_private_pages', $caps );
	}

	public function test_private_read_is_not_granted_in_admin() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->set_static( 'in_main_query', true );
		set_current_screen( 'dashboard' );

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'read_private_pages' ) );

		set_current_screen( 'front' );
		$this->assertArrayNotHasKey( 'read_private_pages', $caps );
	}

	public function test_only_read_private_caps_are_granted() {
		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$this->set_static( 'in_main_query', true );

		$caps = Embed_Access::allow_private_read( array( 'read' => true ), array( 'edit_post', 'edit_pages' ) );

		$this->assertArrayNotHasKey( 'edit_post', $caps );
		$this->assertArrayNotHasKey( 'edit_pages', $caps );
	}

	/*
	 * Which pages a token may open.
	 */

	public function test_the_template_itself_is_not_redirected() {
		$url = $this->token_url( get_permalink( $this->template_id ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( '', $this->redirect_target() );
	}

	public function test_the_private_template_is_found_by_the_main_query() {
		$url = $this->token_url( get_permalink( $this->template_id ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( $this->template_id, get_queried_object_id() );
	}

	public function test_another_page_is_redirected() {
		$other = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$url   = $this->token_url( get_permalink( $other ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( home_url( '/' ), $this->redirect_target() );
	}

	/**
	 * Regression: a token must not open a search (that listed every private post).
	 */
	public function test_search_with_a_token_is_redirected() {
		self::factory()->post->create(
			array(
				'post_status' => 'private',
				'post_title'  => 'Umy secret',
				'post_author' => $this->admin_id, // Someone else's private post.
			)
		);
		$url = $this->token_url( home_url( '/?s=Umy' ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( home_url( '/' ), $this->redirect_target() );
	}

	/**
	 * Regression: a token must not open a feed.
	 */
	public function test_feed_with_a_token_is_redirected() {
		$url = $this->token_url( home_url( '/?feed=rss2' ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( home_url( '/' ), $this->redirect_target() );
	}

	/**
	 * Regression: a token must not open an archive.
	 */
	public function test_archive_with_a_token_is_redirected() {
		$url = $this->token_url( home_url( '/?cat=1' ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$this->assertSame( home_url( '/' ), $this->redirect_target() );
	}

	/**
	 * Other queries on the template page must not see private posts.
	 */
	public function test_secondary_queries_do_not_see_private_posts() {
		self::factory()->post->create(
			array(
				'post_status' => 'private',
				'post_title'  => 'Umy secret',
				'post_author' => $this->admin_id, // Someone else's private post.
			)
		);
		$url = $this->token_url( get_permalink( $this->template_id ) );
		$this->use_url( $url );
		$this->go_to( $url );

		$secondary = new WP_Query(
			array(
				'post_type'   => 'post',
				'post_status' => array( 'publish', 'private' ),
				'perm'        => 'readable', // Only what the current user may read.
				's'           => 'Umy secret',
			)
		);

		$this->assertSame( 0, $secondary->found_posts );
	}

	public function test_bricks_templates_are_public_only_with_a_token() {
		$this->assertSame( array(), Embed_Access::allow_bricks_template( array() ) );

		$this->use_url( $this->token_url( home_url( '/' ) ) );
		$settings = Embed_Access::allow_bricks_template( array() );

		$this->assertTrue( $settings['publicTemplates'] );
	}
}
