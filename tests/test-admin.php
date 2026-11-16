<?php
/**
 * Tests for the admin side: saving settings, dashboard widgets, template list.
 *
 * @package Welcomify
 */

use UMY_WDW_Plugin\Admin;

/**
 * Admin tests.
 */
class Test_Admin extends WP_UnitTestCase {

	/**
	 * Admin instance created on an admin screen (its constructor only registers hooks in admin).
	 *
	 * @var Admin
	 */
	private $admin;

	public function set_up() {
		parent::set_up();
		set_current_screen( 'dashboard' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->admin = new Admin();
		$_POST       = array();
	}

	public function tear_down() {
		$_POST = array();
		set_current_screen( 'front' );
		parent::tear_down();
	}

	private function valid_post( array $templates ) {
		$_POST = array(
			'umy_wdw_settings_nonce' => wp_create_nonce( 'umy_wdw_settings' ),
			'umy_wdw_templates'      => $templates,
		);
	}

	/*
	 * Saving settings.
	 */

	public function test_settings_are_saved_and_sanitized() {
		$this->valid_post(
			array(
				'editor' => array(
					'template'     => '12abc',
					'site'         => '-3',
					'dismissible'  => 'on',
					'hide_widgets' => '1',
				),
			)
		);

		$this->admin->update_settings();

		$this->assertSame(
			array(
				'editor' => array(
					'template'     => 12,
					'site'         => 3,
					'dismissible'  => 1,
					'hide_widgets' => 1,
				),
			),
			get_option( '_umy_wdw_templates' )
		);
		$this->assertSame( 1, (int) get_transient( 'umy_wdw_settings_saved' ) );
	}

	public function test_missing_checkboxes_are_saved_as_off() {
		$this->valid_post( array( 'author' => array( 'template' => '5' ) ) );

		$this->admin->update_settings();

		$saved = get_option( '_umy_wdw_templates' );
		$this->assertSame( 0, $saved['author']['dismissible'] );
		$this->assertSame( 0, $saved['author']['hide_widgets'] );
	}

	public function test_unknown_roles_and_bad_values_are_ignored() {
		$this->valid_post(
			array(
				'editor'        => array( 'template' => '7' ),
				'not_a_role'    => array( 'template' => '8' ),
				'subscriber'    => 'not-an-array',
			)
		);

		$this->admin->update_settings();

		$this->assertSame( array( 'editor' ), array_keys( get_option( '_umy_wdw_templates' ) ) );
	}

	public function test_a_non_array_post_is_ignored() {
		update_option( '_umy_wdw_templates', array( 'keep' => 'me' ) );
		$_POST = array(
			'umy_wdw_settings_nonce' => wp_create_nonce( 'umy_wdw_settings' ),
			'umy_wdw_templates'      => 'string',
		);

		$this->admin->update_settings();

		$this->assertSame( array( 'keep' => 'me' ), get_option( '_umy_wdw_templates' ) );
	}

	public function test_nothing_is_saved_without_a_valid_nonce() {
		$_POST = array(
			'umy_wdw_settings_nonce' => 'wrong',
			'umy_wdw_templates'      => array( 'editor' => array( 'template' => '7' ) ),
		);

		$this->admin->update_settings();

		$this->assertFalse( get_option( '_umy_wdw_templates' ) );
	}

	public function test_nothing_is_saved_without_manage_options() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->valid_post( array( 'editor' => array( 'template' => '7' ) ) );

		$this->admin->update_settings();

		$this->assertFalse( get_option( '_umy_wdw_templates' ) );
	}

	/*
	 * Dashboard widgets.
	 */

	public function test_dashboard_widgets_are_emptied_but_the_screen_entry_stays() {
		global $wp_meta_boxes;
		$wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] = array( 'id' => 'dashboard_activity' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Test fixture.

		$this->admin->remove_dashboard_widgets();

		// The entry must remain, or WordPress prints no Screen Options (and no Welcome checkbox).
		$this->assertArrayHasKey( 'dashboard', $wp_meta_boxes );
		$this->assertSame( array(), $wp_meta_boxes['dashboard']['normal'] );
		$this->assertSame( array(), $wp_meta_boxes['dashboard']['side'] );
	}

	/*
	 * Draft pages, Screen Options.
	 */

	/** Runs admin_init on the dashboard for the current user and returns whether the panel is active. */
	private function dashboard_panel_active( array $role_settings, $role ) {
		update_option( '_umy_wdw_templates', array( $role => $role_settings ) );
		$GLOBALS['pagenow'] = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Test fixture.

		$admin               = new Admin();
		$admin->current_role = $role;
		$admin->admin_init();

		$prop = new ReflectionProperty( Admin::class, 'panel_active' );
		$prop->setAccessible( true );

		return array( $admin, $prop->getValue( $admin ) );
	}

	public function test_a_draft_is_not_shown_to_a_role_that_cannot_see_it() {
		$draft = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_author' => get_current_user_id() ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );

		list( , $active ) = $this->dashboard_panel_active( array( 'template' => $draft, 'hide_widgets' => 1 ), 'contributor' );

		$this->assertFalse( $active, 'Contributors would see the home page instead of a draft, so they keep the normal dashboard.' );
	}

	public function test_a_draft_is_shown_to_an_editor() {
		$draft = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'draft' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		list( , $active ) = $this->dashboard_panel_active( array( 'template' => $draft, 'hide_widgets' => 1 ), 'editor' );

		$this->assertTrue( $active );
	}

	public function test_empty_screen_options_are_hidden() {
		add_role( 'umy_test_viewer', 'Viewer', array( 'read' => true ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'umy_test_viewer' ) ) );
		$page   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$screen = WP_Screen::get( 'dashboard' );

		// Not dismissible and widgets removed: nothing to show in Screen Options.
		list( $admin ) = $this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 0, 'hide_widgets' => 1 ), 'umy_test_viewer' );
		$this->assertFalse( $admin->screen_options_show( true, $screen ) );

		// Dismissible: the Welcome checkbox is there, so the button stays.
		list( $admin ) = $this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 1, 'hide_widgets' => 1 ), 'umy_test_viewer' );
		$this->assertTrue( $admin->screen_options_show( true, $screen ) );

		// Widgets kept: their checkboxes are there, so the button stays.
		list( $admin ) = $this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 0, 'hide_widgets' => 0 ), 'umy_test_viewer' );
		$this->assertTrue( $admin->screen_options_show( true, $screen ) );

		remove_role( 'umy_test_viewer' );
	}

	/*
	 * WordPress' own Welcome panel for roles with edit_theme_options (e.g. a copy of Administrator).
	 */

	/** Logs in a new user whose role is a copy of Administrator (not the site creator). */
	private function login_admin_copy() {
		add_role( 'umy_test_admin_copy', 'Admin copy', get_role( 'administrator' )->capabilities );
		$user_id = self::factory()->user->create( array( 'role' => 'umy_test_admin_copy' ) );
		wp_set_current_user( $user_id );
		return $user_id;
	}

	public function test_welcome_panel_is_shown_by_default_for_an_admin_copy_role() {
		$user_id = $this->login_admin_copy();
		$page    = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 1, 'hide_widgets' => 1 ), 'umy_test_admin_copy' );

		// WordPress hides the panel when this is missing or 0; Welcomify answers 1 for a new user.
		$this->assertSame( 1, (int) get_user_meta( $user_id, 'show_welcome_panel', true ) );
		remove_role( 'umy_test_admin_copy' );
	}

	public function test_a_dismissible_panel_stays_closed_once_the_user_closed_it() {
		$user_id = $this->login_admin_copy();
		$page    = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_user_meta( $user_id, 'show_welcome_panel', 0 ); // The user closed it.

		$this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 1, 'hide_widgets' => 1 ), 'umy_test_admin_copy' );

		$this->assertSame( 0, (int) get_user_meta( $user_id, 'show_welcome_panel', true ) );
		remove_role( 'umy_test_admin_copy' );
	}

	public function test_a_non_dismissible_panel_is_always_shown() {
		$user_id = $this->login_admin_copy();
		$page    = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_user_meta( $user_id, 'show_welcome_panel', 0 );

		list( $admin ) = $this->dashboard_panel_active( array( 'template' => $page, 'dismissible' => 0, 'hide_widgets' => 1 ), 'umy_test_admin_copy' );

		$this->assertSame( 1, (int) get_user_meta( $user_id, 'show_welcome_panel', true ) );
		// Nothing left to switch in Screen Options, so the button is hidden too.
		$this->assertFalse( $admin->screen_options_show( true, WP_Screen::get( 'dashboard' ) ) );
		remove_role( 'umy_test_admin_copy' );
	}

	public function test_welcome_panel_setting_is_untouched_without_a_template() {
		$user_id = $this->login_admin_copy();

		$this->dashboard_panel_active( array( 'template' => 0 ), 'umy_test_admin_copy' );

		$this->assertSame( '', get_user_meta( $user_id, 'show_welcome_panel', true ) );
		remove_role( 'umy_test_admin_copy' );
	}

	/*
	 * Template list.
	 */

	public function test_elementor_kit_is_not_offered_as_a_template() {
		register_post_type( 'elementor_library', array( 'public' => true ) );
		$kit      = self::factory()->post->create( array( 'post_type' => 'elementor_library', 'post_title' => 'Default Kit' ) );
		$template = self::factory()->post->create( array( 'post_type' => 'elementor_library', 'post_title' => 'Dashboard' ) );
		update_post_meta( $kit, '_elementor_template_type', 'kit' );
		update_post_meta( $template, '_elementor_template_type', 'page' );

		$active = new ReflectionProperty( UMY_WDW_Plugin::class, 'elementor_active' );
		$active->setAccessible( true );
		$active->setValue( null, true );

		$method = new ReflectionMethod( Admin::class, 'get_templates' );
		$method->setAccessible( true );
		$list = $method->invoke( $this->admin );

		$active->setValue( null, false );
		unregister_post_type( 'elementor_library' );

		$this->assertArrayHasKey( $template, $list['elementor_templates'] );
		$this->assertArrayNotHasKey( $kit, $list['elementor_templates'] );
	}

	public function test_status_suffix_is_translated_label() {
		$method = new ReflectionMethod( Admin::class, 'get_status_suffix' );
		$method->setAccessible( true );

		$draft     = get_post( self::factory()->post->create( array( 'post_status' => 'draft' ) ) );
		$published = get_post( self::factory()->post->create( array( 'post_status' => 'publish' ) ) );

		$this->assertSame( ' [' . get_post_status_object( 'draft' )->label . ']', $method->invoke( $this->admin, $draft ) );
		$this->assertSame( '', $method->invoke( $this->admin, $published ) );
	}
}
