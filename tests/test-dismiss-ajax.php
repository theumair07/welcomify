<?php
/**
 * Tests for dismissing / restoring the welcome panel over AJAX.
 *
 * @package Welcomify
 */

use UMY_WDW_Plugin\Admin;

/**
 * AJAX dismiss tests.
 *
 * @group ajax
 */
class Test_Dismiss_Ajax extends WP_Ajax_UnitTestCase {

	/**
	 * Logged-in user with a custom role.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Template assigned to the role.
	 *
	 * @var int
	 */
	private $template_id;

	public function set_up() {
		parent::set_up();

		add_role( 'umy_test_viewer', 'Viewer', array( 'read' => true ) );
		$this->user_id     = self::factory()->user->create( array( 'role' => 'umy_test_viewer' ) );
		$this->template_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		wp_set_current_user( $this->user_id );

		// Register the AJAX handler like on a real admin request.
		set_current_screen( 'dashboard' );
		new Admin();
	}

	public function tear_down() {
		remove_role( 'umy_test_viewer' );
		set_current_screen( 'front' );
		parent::tear_down();
	}

	private function set_role_settings( $dismissible ) {
		update_option(
			'_umy_wdw_templates',
			array(
				'umy_test_viewer' => array(
					'template'     => $this->template_id,
					'site'         => 0,
					'dismissible'  => $dismissible ? 1 : 0,
					'hide_widgets' => 1,
				),
			)
		);
	}

	private function call( $visible ) {
		$_POST['nonce']   = wp_create_nonce( 'umy_wdw_dismiss' );
		$_POST['visible'] = $visible ? '1' : '0';

		try {
			$this->_handleAjax( 'umy_wdw_dismiss' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e ); // Expected: wp_send_json_*() ends the request.
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		return json_decode( $this->_last_response, true );
	}

	public function test_dismiss_stores_the_template_id() {
		$this->set_role_settings( true );

		$response = $this->call( false );

		$this->assertTrue( $response['success'] );
		$this->assertSame( $this->template_id, (int) get_user_meta( $this->user_id, 'umy_wdw_dismissed', true ) );
	}

	public function test_showing_again_clears_the_flag_and_returns_a_fresh_url() {
		$this->set_role_settings( true );
		update_user_meta( $this->user_id, 'umy_wdw_dismissed', $this->template_id );

		$response = $this->call( true );

		$this->assertTrue( $response['success'] );
		$this->assertSame( '', get_user_meta( $this->user_id, 'umy_wdw_dismissed', true ) );
		$this->assertStringContainsString( 'umy_wdw_token=', $response['data']['url'] );
	}

	public function test_dismiss_is_refused_when_the_role_is_not_dismissible() {
		$this->set_role_settings( false );

		$response = $this->call( false );

		$this->assertFalse( $response['success'] );
		$this->assertSame( '', get_user_meta( $this->user_id, 'umy_wdw_dismissed', true ) );
	}

	public function test_dismiss_needs_a_valid_nonce() {
		$this->set_role_settings( true );
		$_POST['nonce']   = 'wrong';
		$_POST['visible'] = '0';

		$this->expectException( WPAjaxDieStopException::class );
		$this->_handleAjax( 'umy_wdw_dismiss' );
	}
}
