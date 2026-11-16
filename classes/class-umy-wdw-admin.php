<?php
/**
 * Admin screens: settings page, Getting Started page and the dashboard welcome panel.
 *
 * @package Welcomify
 */

namespace UMY_WDW_Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin logic.
 */
final class Admin {
	/**
	 * Holds the current class object.
	 *
	 * @since 1.0.0
	 * @var Admin|null
	 */
	public static $instance;

	/**
	 * Holds the settings page slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $settings_page;

	/**
	 * Holds the settings page title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $settings_title;

	/**
	 * Holds the settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $settings;

	/**
	 * Holds the user roles.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $roles;

	/**
	 * Holds the current user role.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $current_role;

	/**
	 * Holds the Getting Started page hook.
	 *
	 * @since 1.5.0
	 * @var string
	 */
	public $start_page_hook;

	/**
	 * Whether the Welcomify panel is shown on this dashboard request.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private $panel_active = false;

	/**
	 * Whether the other dashboard widgets are removed on this dashboard request.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private $widgets_hidden = false;

	/**
	 * Initializes the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$this->settings_page  = 'umy-wdw-settings';
		$this->settings_title = apply_filters( 'umy_wdw_settings_title', __( 'Welcomify - Custom Welcome Dashboard', 'welcomify' ) );
		$this->settings       = $this->get_settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_umy_wdw_dismiss', array( $this, 'ajax_dismiss' ) );
		add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 2 );
		add_filter( 'screen_options_show_screen', array( $this, 'screen_options_show' ), 10, 2 );
	}

	/**
	 * Whether the "Welcome" panel toggle / own dismiss is needed for the current user.
	 *
	 * Roles with edit_theme_options use WordPress' own Welcome checkbox and
	 * dismiss. Other roles get Welcomify's own, because core hides both from them.
	 *
	 * @since 1.5.0
	 * @param string $role Role slug.
	 * @return bool
	 */
	private function uses_own_panel_controls( $role ) {
		return $this->panel_active
			&& ! current_user_can( 'edit_theme_options' )
			&& ! empty( $this->settings[ $role ]['template'] );
	}

	/**
	 * Hides the Screen Options button when its panel would be empty.
	 *
	 * That is the case for a role without edit_theme_options (no WordPress Welcome checkbox),
	 * whose panel is not dismissible (no Welcomify Welcome checkbox) and whose other widgets
	 * are removed (no widget checkboxes).
	 *
	 * @since 1.5.0
	 * @param bool       $show   Whether to show the Screen Options button.
	 * @param \WP_Screen $screen Current screen.
	 * @return bool
	 */
	public function screen_options_show( $show, $screen ) {
		if ( ! $show || ! $screen || 'dashboard' !== $screen->id ) {
			return $show;
		}

		$role = $this->get_user_role();

		if ( $this->uses_own_panel_controls( $role ) && $this->widgets_hidden && empty( $this->settings[ $role ]['dismissible'] ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Adds a "Welcome" checkbox to Screen Options for roles that can't see WordPress' own.
	 *
	 * Lets users bring the panel back after dismissing it, the same way the
	 * Welcome checkbox works for administrators.
	 *
	 * @since 1.5.0
	 * @param string          $settings Existing Screen Options HTML.
	 * @param \WP_Screen|null $screen Current screen.
	 * @return string
	 */
	public function screen_settings( $settings, $screen ) {
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return $settings;
		}

		$role = $this->get_user_role();

		if ( ! $this->uses_own_panel_controls( $role ) || empty( $this->settings[ $role ]['dismissible'] ) ) {
			return $settings;
		}

		ob_start();
		?>
		<fieldset class="metabox-prefs umy-wdw-screen-option">
			<legend><?php esc_html_e( 'Welcome panel', 'welcomify' ); ?></legend>
			<label for="umy-wdw-panel-hide">
				<input type="checkbox" id="umy-wdw-panel-hide" <?php checked( ! $this->is_dismissed( $role ) ); ?> />
				<?php esc_html_e( 'Welcome', 'welcomify' ); ?>
			</label>
		</fieldset>
		<?php
		return $settings . ob_get_clean();
	}

	/**
	 * Loads the dashboard script for roles that use Welcomify's own panel controls.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	private function enqueue_dashboard_script() {
		$role = $this->get_user_role();

		if ( ! $this->uses_own_panel_controls( $role ) ) {
			return;
		}

		wp_enqueue_script(
			'umy-wdw-dashboard',
			UMY_WDW_URL . 'assets/js/dashboard-panel.js',
			array( 'jquery' ),
			UMY_WDW_VER,
			true
		);

		wp_localize_script(
			'umy-wdw-dashboard',
			'umyWdwDashboard',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'umy_wdw_dismiss' ),
			)
		);
	}

	/**
	 * Whether the current user has dismissed the panel for the template assigned to their role.
	 *
	 * Used for roles that cannot use WordPress' own welcome panel dismiss
	 * (it needs the edit_theme_options capability). Dismissal is stored per
	 * template, so assigning a new template shows the panel again.
	 *
	 * @since 1.5.0
	 * @param string $role Role slug.
	 * @return bool
	 */
	private function is_dismissed( $role ) {
		if ( empty( $this->settings[ $role ]['dismissible'] ) || empty( $this->settings[ $role ]['template'] ) ) {
			return false;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'umy_wdw_dismissed', true );

		return ! empty( $dismissed ) && absint( $dismissed ) === absint( $this->settings[ $role ]['template'] );
	}

	/**
	 * AJAX: dismiss the welcome panel for the current user.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function ajax_dismiss() {
		check_ajax_referer( 'umy_wdw_dismiss', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( null, 403 );
		}

		$this->settings = $this->get_settings();
		$role           = $this->get_user_role();

		// Only allowed when the role is set to dismissible and has a template.
		if ( empty( $this->settings[ $role ]['dismissible'] ) || empty( $this->settings[ $role ]['template'] ) ) {
			wp_send_json_error( null, 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$visible = ! empty( $_POST['visible'] );

		if ( $visible ) {
			delete_user_meta( get_current_user_id(), 'umy_wdw_dismissed' );

			// Fresh iframe URL (its token is short-lived), so the panel can be shown again without a page reload.
			wp_send_json_success( array( 'url' => $this->get_embed_url( $role ) ) );
		}

		update_user_meta( get_current_user_id(), 'umy_wdw_dismissed', absint( $this->settings[ $role ]['template'] ) );

		wp_send_json_success();
	}

	/**
	 * Enqueue admin styles and scripts on settings page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Dashboard: panel controls for roles that can't use WordPress' own.
		if ( 'index.php' === $hook ) {
			$this->enqueue_dashboard_script();
			return;
		}

		// Only load on our plugin pages.
		$is_settings = ( 'toplevel_page_' . $this->settings_page ) === $hook;
		$is_start    = ! empty( $this->start_page_hook ) && $this->start_page_hook === $hook;

		if ( ! $is_settings && ! $is_start ) {
			return;
		}

		wp_enqueue_style(
			'umy-wdw-admin-settings',
			UMY_WDW_URL . 'assets/css/admin-settings.css',
			array(),
			UMY_WDW_VER
		);

		// The Getting Started guide only needs the styles.
		if ( $is_start ) {
			return;
		}

		// Enqueue notice countdown script (for success message auto-dismiss).
		wp_enqueue_script(
			'umy-wdw-admin-notice',
			UMY_WDW_URL . 'assets/js/admin-notice.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue unsaved-changes tracking (sticky save bar + leave warning).
		wp_enqueue_script(
			'umy-wdw-admin-settings-form',
			UMY_WDW_URL . 'assets/js/admin-settings-form.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue custom dropdown script.
		wp_enqueue_script(
			'umy-wdw-admin-dropdown',
			UMY_WDW_URL . 'assets/js/admin-dropdown.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue multisite handler script.
		if ( is_multisite() ) {
			wp_enqueue_script(
				'umy-wdw-admin-multisite',
				UMY_WDW_URL . 'assets/js/admin-multisite.js',
				array( 'jquery' ),
				UMY_WDW_VER,
				true
			);
		}
	}

	/**
	 * Initializes admin related logic.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_init() {
		$this->update_settings();

		global $pagenow;

		if ( 'index.php' !== $pagenow ) {
			return;
		}

		$settings = $this->settings;
		$role     = $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$template_id  = $settings[ $role ]['template'];
				$site_id      = isset( $settings[ $role ]['site'] ) ? $settings[ $role ]['site'] : '';
				$is_multisite = is_multisite();

				// Handle multisite - switch to the correct site to check post status.
				if ( ! empty( $site_id ) && $is_multisite ) {
					switch_to_blog( $site_id );
				}

				// Verify if the template exists and is accessible (not trashed or deleted).
				$post_status    = get_post_status( $template_id );
				$valid_statuses = array( 'publish', 'draft', 'private' );
				$is_valid       = $post_status && in_array( $post_status, $valid_statuses, true );

				// WordPress only shows a draft to users who may edit it. Anyone else would get the
				// home page inside the panel, so they keep the normal dashboard instead.
				if ( $is_valid && 'draft' === $post_status && ! current_user_can( 'edit_post', $template_id ) ) {
					$is_valid = false;
				}

				if ( ! empty( $site_id ) && $is_multisite ) {
					restore_current_blog();
				}

				// If template doesn't exist, is trashed or can't be seen, show default WordPress welcome.
				if ( ! $is_valid ) {
					return;
				}

				$this->panel_active = true;

				// "Hide other widgets": the admin's choice. The other dashboard widgets are removed for this role,
				// also when the user dismisses the panel. On unless switched off; roles saved before this
				// option existed keep the clean dashboard they had.
				if ( ! isset( $settings[ $role ]['hide_widgets'] ) || ! empty( $settings[ $role ]['hide_widgets'] ) ) {
					$this->widgets_hidden = true;
					add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ), PHP_INT_MAX );
				}

				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_action( 'welcome_panel', array( $this, 'welcome_panel' ) );

				// custom fallback for the users who don't have
				// enough capabilities to display welcome panel.
				if ( ! current_user_can( 'edit_theme_options' ) ) {
					add_action( 'admin_notices', array( $this, 'welcome_panel' ) );
				}
			}
		}
	}

	/**
	 * Removes every dashboard widget (they are then also missing from Screen Options).
	 *
	 * Runs last on wp_dashboard_setup so widgets added by other plugins are removed too.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function remove_dashboard_widgets() {
		global $wp_meta_boxes;

		// Keep the (empty) dashboard entry instead of unsetting it: WordPress only prints the
		// Screen Options panel, with its Welcome checkbox, when this entry exists.
		$wp_meta_boxes['dashboard'] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentional, see the comment above.
			'normal'  => array(),
			'side'    => array(),
			'column3' => array(),
			'column4' => array(),
		);
	}

	/**
	 * Renders welcome panel.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function welcome_panel() {
		include UMY_WDW_DIR . 'includes/welcome-panel.php';
	}

	/**
	 * Add Dashboard Welcome to admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		global $wp_roles;

		$this->roles        = $wp_roles->get_names();
		$this->current_role = $this->get_user_role();

		if ( is_multisite() ) {
			$hide_settings = get_blog_option( 1, 'umy_wdw_hide_from_subsites' );

			if ( $hide_settings && 1 !== get_current_blog_id() ) {
				return;
			}
		}

		if ( current_user_can( 'manage_options' ) ) {

			$title = __( 'Welcomify', 'welcomify' );
			$cap   = 'manage_options';
			$slug  = $this->settings_page;
			$func  = array( $this, 'render_settings' );

			// Menu icon: an SVG data URI, the format WordPress documents for add_menu_page(), so the
			// icon follows the admin colour scheme.
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Static, readable SVG markup, not obfuscation.
			$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 250"><path fill="#a7aaad" d="M16.416 35.431c2.914-3.588 2.903-3.559 3.147-3.875C22.428 27.85 40.163 4.912 71.5 4.522c115.494-1.436 118.892.271 128.052 4.873 50.239 25.24 44.028 51.832 44.028 124.105 0 34.054 7.458 78.842-38.178 102.756-14.231 7.457-14.856 7.839-138.901 7.213l-2.001.028c-2.413-.897-2.324-.786-5.001-.99-2.501-.971-54.917-12.786-54.993-71.007C4.358 57.64 3.125 56.196 16.416 35.431l167.092 24.081c-.044 4.561-14.695 6.084-9.755 17.66 1.582 3.709 8.187 35.831.993 52.65-1.776 4.154-.637 4.385-2.526 8.58-12.656 28.102-31.009 32.5-37.664 4.091-6.23-26.595 9.994-31.008 10.031-48.992.006-2.425.057-27.113-11.048-28.231-23.383-2.353-14.64 48.056-19.319 58.132-17.697 38.104-39.212 50.291-38.598 24.11.275-11.729 32.607-100.762-15.117-85.003-11.002 3.633-16.804 25.549-10.853 19.162 5.63-6.042 12.703 3.27 12.727 4.841.235 15.478-1.182 15.263-8.112 53.909-.682 3.804-8.338 46.497 14.233 46.172.237-.003 3.076-.044-.005 2.963 3.723.405 3.699.41 4.021.437.618.053.5-.192 1.138-.138-.357-2.242-1.377-3.168.857-3.269 25.981-1.17 34.882-39.365 40.902-39.347l1.199-.001c5.736.884 2.335 5.984 5.384 13.943 6.571 17.154 5.248 29.336 34.497 24.306 3.508-.603 26.324-12.515 37.834-47.051 12.383-37.152 9.613-75.388-5.82-76.992-2.77-.288-2.772-.009-2.978-.128-1.328-.759.05-1.549-1.182-2.254l-.839.45L16.416 35.431l167.092 24.081c-5.952.854-7.948 3.295-12.047 7.957-4.707 5.352 12.242 41.722-3.188 72.925-23.191 46.899-37.094-7.171-31.524-17.759 2.118-4.025 21.699-41.246 3.385-56.748-19.621-16.609-29.99 25.211-27.308 41.601 3.877 23.697-25.82 52.694-30.324 51.964-13.083-2.121 8.179-38.153 6.975-81.951-.749-27.235-45.588-28.132-46.006 13.004-.058 5.748 4.109-7.452 10.039-7.926 15.965-1.277-1.998 34.816-4.224 74.837.945 1.529.992 1.564.001 3.168 1.361 11.967.513 23.205 19.208 28.972 3.081-3.007.242-2.966.005-2.963-22.571.325-14.915-42.368-14.233-46.172 6.93-38.646 8.347-38.431 8.112-53.909-.024-1.571-7.097-10.883-12.727-4.841-5.951 6.387-.149-15.529 10.853-19.162 47.724-15.759 15.392 73.274 15.117 85.003-.614 26.181 20.901 13.994 38.598-24.11 4.679-10.076-4.064-60.485 19.319-58.132 11.105 1.118 11.054 25.806 11.048 28.231-.037 17.984-16.261 22.397-10.031 48.992 6.655 28.409 25.008 24.011 37.664-4.091 1.889-4.195.75-4.426 2.526-8.58 7.194-16.819.589-48.941-.993-52.65-4.94-11.576 9.711-13.099 9.755-17.66L16.416 35.431l167.931 23.631c1.232.705-.146 1.495 1.182 2.254.206.119.208-.16 2.978.128 15.433 1.604 18.203 39.84 5.82 76.992-11.51 34.536-34.326 46.448-37.834 47.051-29.249 5.03-27.926-7.152-34.497-24.306-3.049-7.959.352-13.059-5.384-13.943.539 4.385 5.991 48.794 35.824 42.032 53.812-12.197 69.634-138.588 31.911-130.208L16.416 35.431l98.997 111.808c-6.02-.018-14.921 38.177-40.902 39.347-2.234.101-1.214 1.027-.857 3.269 13.056-2.509 27.045-7.427 41.759-42.616L16.416 35.431l32.87 121.985.001 3.168c.991-1.604.944-1.639-.001-3.168L16.416 35.431Z" transform="translate(4 4)"/></svg>' );

			// Add top-level menu (position 81 = below Settings).
			add_menu_page( $title, $title, $cap, $slug, $func, $icon, 81 );

			// Add "Settings" submenu (same as main menu for cleaner structure).
			add_submenu_page(
				$slug,
				__( 'Settings', 'welcomify' ),
				__( 'Settings', 'welcomify' ),
				$cap,
				$slug,
				$func
			);

			// Add "Getting Started" submenu.
			$this->start_page_hook = add_submenu_page(
				$slug,
				__( 'Getting Started', 'welcomify' ),
				__( 'Getting Started', 'welcomify' ),
				$cap,
				'umy-wdw-start',
				array( $this, 'render_start' )
			);

			// List Getting Started first (the menu then opens it first).
			global $submenu;
			if ( isset( $submenu[ $slug ] ) ) {
				foreach ( $submenu[ $slug ] as $index => $item ) {
					if ( 'umy-wdw-start' === $item[2] ) {
						unset( $submenu[ $slug ][ $index ] );
						array_unshift( $submenu[ $slug ], $item );
						break;
					}
				}
			}
		}
	}

	/**
	 * Renders the Getting Started guide.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function render_start() {
		include UMY_WDW_DIR . 'includes/getting-started.php';
	}

	/**
	 * Renders settings content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings() {
		$title        = $this->settings_title;
		$form_action  = $this->get_form_action();
		$roles        = $this->roles;
		$current_role = $this->current_role;
		$templates    = $this->get_templates();
		$settings     = $this->get_settings();

		include UMY_WDW_DIR . 'includes/admin-settings.php';
	}

	/**
	 * Builds the iframe URL for the template assigned to a role.
	 *
	 * Includes the embed flags and a fresh signed token (see Embed_Access).
	 *
	 * @since 1.5.0
	 * @param string $role Role slug.
	 * @return string Empty string when the role has no valid template.
	 */
	private function get_embed_url( $role ) {
		if ( empty( $this->settings[ $role ]['template'] ) ) {
			return '';
		}

		$template_id  = $this->settings[ $role ]['template'];
		$site_id      = isset( $this->settings[ $role ]['site'] ) ? $this->settings[ $role ]['site'] : '';
		$is_multisite = is_multisite();

		// Handle multisite.
		if ( ! empty( $site_id ) && $is_multisite ) {
			switch_to_blog( $site_id );
		}

		// Get the page URL.
		$page_url = get_permalink( $template_id );

		if ( ! empty( $site_id ) && $is_multisite ) {
			restore_current_blog();
		}

		if ( ! $page_url ) {
			return '';
		}

		// Always pass hide parameters (auto-hide enabled).
		$url_params = array(
			'umy_wdw_embed' => '1',
			'hide_header'   => '1',
			'hide_footer'   => '1',
			'hide_title'    => '1',
		);

		// Add parameters to page URL.
		$page_url = add_query_arg( $url_params, $page_url );

		// Signed, short-lived token so Bricks/Elementor let this iframe request
		// through for roles without builder access (e.g. custom roles).
		return Embed_Access::add_token( $page_url, $template_id );
	}

	/**
	 * Renders template content in welcome panel using iframe embed.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_template() {
		$settings = $this->settings;
		$role     = $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$dismissible = ! empty( $settings[ $role ]['dismissible'] );
				// On unless switched off. Roles saved before this option existed keep their clean dashboard.
				$hide_widgets = ! isset( $settings[ $role ]['hide_widgets'] ) || ! empty( $settings[ $role ]['hide_widgets'] );
				$page_url     = $this->get_embed_url( $role );

				if ( ! $page_url ) {
					return;
				}

				// A dismissed panel (roles using Welcomify's own controls) is still printed but hidden.
				// Its iframe is not loaded until the user turns the panel back on in Screen Options.
				$deferred = ! current_user_can( 'edit_theme_options' ) && $this->is_dismissed( $role );

				// Add inline styles via WordPress API.
				$css = '
/* Hide WordPress Dashboard title while the Welcomify panel is showing */
body:has(#welcome-panel:not(.hidden) #umy-wdw-dashboard-welcome) #wpbody-content>.wrap>h1,
body:has(#welcome-panel:not(.hidden) #umy-wdw-dashboard-welcome) #wpbody-content h1.open,
body:has(#welcome-panel:not(.hidden) #umy-wdw-dashboard-welcome) .wrap>h1:first-child,
body:has(#umy-wdw-dashboard-welcome:not(#welcome-panel *):not(.umy-wdw-is-dismissed)) #wpbody-content>.wrap>h1,
body:has(#umy-wdw-dashboard-welcome:not(#welcome-panel *):not(.umy-wdw-is-dismissed)) #wpbody-content h1.open,
body:has(#umy-wdw-dashboard-welcome:not(#welcome-panel *):not(.umy-wdw-is-dismissed)) .wrap>h1:first-child {
    display: none !important;
}

.umy-wdw-embed-container {
    width: 100%;
    min-height: 400px;
    background: transparent;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
}

.umy-wdw-embed-iframe {
    width: 100%;
    min-height: 400px;
    border: none;
    display: block;
}

.welcome-panel {
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
}

.welcome-panel::before,
.welcome-panel::after {
    display: none !important;
}';

				if ( $hide_widgets ) {
					// The widgets themselves are removed on the server (remove_dashboard_widgets()), so they are
					// not in Screen Options either. This only hides the empty dashed boxes WordPress leaves
					// behind and the now pointless column layout option.
					$css .= '

/* Other dashboard widgets are removed: hide the empty widget area */
#dashboard-widgets-wrap,
#adv-settings .columns-prefs {
    display: none !important;
}';
				}

				if ( ! $dismissible ) {
					$css .= '
.welcome-panel .welcome-panel-close {
    display: none !important;
}';
				} else {
					$css .= '
/* Dismiss button styling - simple pink background with red text */
.welcome-panel .welcome-panel-close {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    padding: 4px 10px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    font-size: 12px !important;
    transition: all 0.2s ease, opacity 0.15s ease-in-out !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.welcome-panel .welcome-panel-close.umy-wdw-visible {
    opacity: 1 !important;
    pointer-events: auto !important;
}

.welcome-panel .welcome-panel-close:hover {
    background: #fecaca !important;
}

.welcome-panel .welcome-panel-close::before {
    display: none !important;
}';
				}

				// Register and add inline style.
				wp_register_style( 'umy-wdw-dashboard-embed', false, array(), UMY_WDW_VER );
				wp_enqueue_style( 'umy-wdw-dashboard-embed' );
				wp_add_inline_style( 'umy-wdw-dashboard-embed', $css );
				?>
<div class="umy-wdw-embed-container" id="umy-wdw-embed-wrapper"
	style="opacity: 0; transition: opacity 0.15s ease-in-out;">
	<iframe id="umy-wdw-page-embed" class="umy-wdw-embed-iframe" <?php echo $deferred ? 'data-src' : 'src'; ?>="<?php echo esc_url( $page_url ); ?>"
		scrolling="no" title="<?php esc_attr_e( 'Dashboard Welcome Content', 'welcomify' ); ?>"></iframe>
</div>
				<?php
				// Enqueue embed handler script.
				wp_enqueue_script(
					'umy-wdw-embed-handler',
					UMY_WDW_URL . 'assets/js/embed-handler.js',
					array(),
					UMY_WDW_VER,
					true
				);
			}
		}
	}

	/**
	 * Get setting form action attribute.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_form_action() {
		return admin_url( '/admin.php?page=' . $this->settings_page );
	}

	/**
	 * Check if a page was built with Elementor.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_elementor_page( $post_id ) {
		return \UMY_WDW_Plugin::is_elementor_active() &&
				get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder';
	}

	/**
	 * Check if a page was built with Bricks Builder.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_bricks_page( $post_id ) {
		$bricks_data = get_post_meta( $post_id, '_bricks_page_content_2', true );
		return ! empty( $bricks_data );
	}

	/**
	 * Check if a page was built with Divi Builder.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_divi_page( $post_id ) {
		return get_post_meta( $post_id, '_et_pb_use_builder', true ) === 'on';
	}

	/**
	 * Check if a page was built with Beaver Builder.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_beaver_page( $post_id ) {
		return get_post_meta( $post_id, '_fl_builder_enabled', true ) === '1';
	}


	/**
	 * Check if a page was built with Breakdance.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_breakdance_page( $post_id ) {
		$breakdance_data = get_post_meta( $post_id, '_breakdance_data', true );
		return ! empty( $breakdance_data );
	}

	/**
	 * Check if a page has Gutenberg blocks.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function has_gutenberg_blocks( $post_id ) {
		$content = get_post_field( 'post_content', $post_id );
		return function_exists( 'has_blocks' ) && has_blocks( $content );
	}

	/**
	 * Get the content type label for a page.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return string
	 */
	private function get_content_type_label( $post_id, $post_type = 'page' ) {
		// Check for Elementor template post type (only if Elementor is active).
		if ( 'elementor_library' === $post_type && \UMY_WDW_Plugin::is_elementor_active() ) {
			return 'Elementor Template';
		}

		// Check for Bricks template post type (only if Bricks is active).
		if ( 'bricks_template' === $post_type && self::is_bricks_active() ) {
			return 'Bricks Template';
		}

		// Check each page builder - only show label if builder is active
		// This prevents showing builder labels when the plugin is deactivated.

		if ( \UMY_WDW_Plugin::is_elementor_active() && $this->is_elementor_page( $post_id ) ) {
			return 'Elementor';
		}

		if ( self::is_bricks_active() && $this->is_bricks_page( $post_id ) ) {
			return 'Bricks';
		}

		if ( self::is_divi_active() && $this->is_divi_page( $post_id ) ) {
			return 'Divi';
		}

		if ( self::is_beaver_active() && $this->is_beaver_page( $post_id ) ) {
			return 'Beaver Builder';
		}

		if ( self::is_breakdance_active() && $this->is_breakdance_page( $post_id ) ) {
			return 'Breakdance';
		}

		// Default: All WordPress pages without a specific page builder are Gutenberg.
		return 'Gutenberg';
	}

	/**
	 * Check if Bricks Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_bricks_active() {
		return defined( 'BRICKS_VERSION' ) || class_exists( 'Bricks\Theme' );
	}

	/**
	 * Check if Breakdance Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_breakdance_active() {
		return defined( '__BREAKDANCE_VERSION' ) || class_exists( '\Breakdance\Loader' );
	}

	/**
	 * Check if Divi Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_divi_active() {
		return defined( 'ET_BUILDER_VERSION' ) || class_exists( 'ET_Builder_Module' );
	}

	/**
	 * Check if Beaver Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_beaver_active() {
		return defined( 'FL_BUILDER_VERSION' ) || class_exists( 'FLBuilder' );
	}

	/**
	 * Status shown after a title in the template list, for example " [Draft]". Empty for published posts.
	 *
	 * @since 1.5.0
	 * @param \WP_Post $post Post.
	 * @return string
	 */
	private function get_status_suffix( $post ) {
		if ( 'publish' === $post->post_status ) {
			return '';
		}

		$status = get_post_status_object( $post->post_status );
		$label  = ( $status && ! empty( $status->label ) ) ? $status->label : $post->post_status;

		return ' [' . $label . ']';
	}

	/**
	 * Get all WordPress pages, Elementor templates, and Bricks templates.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_templates() {
		// Args for all WordPress pages.
		$page_args = array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'private', 'draft' ),
		);

		// Args for Elementor templates (only if Elementor is active).
		$elementor_templates = array();
		if ( \UMY_WDW_Plugin::is_elementor_active() ) {
			$elementor_args      = array(
				'post_type'      => 'elementor_library',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'private', 'draft' ),
			);
			$elementor_templates = get_posts( $elementor_args );
		}

		// Args for Bricks templates (only if Bricks is active).
		$bricks_templates = array();
		if ( self::is_bricks_active() ) {
			$bricks_args      = array(
				'post_type'      => 'bricks_template',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'private', 'draft' ),
			);
			$bricks_templates = get_posts( $bricks_args );
		}

		$pages = get_posts( $page_args );

		// Multisite support.
		if ( is_multisite() ) {

			$blog_id = get_current_blog_id();

			if ( 1 !== $blog_id ) {
				switch_to_blog( 1 );

				// Get posts from main site.
				$main_pages = get_posts( $page_args );

				if ( \UMY_WDW_Plugin::is_elementor_active() ) {
					$main_elementor = get_posts( $elementor_args );
					foreach ( $main_elementor as $main_post ) {
						$main_post->site_id = 1;
					}
					$elementor_templates = array_merge( $elementor_templates, $main_elementor );
				}

				if ( self::is_bricks_active() ) {
					$main_bricks = get_posts( $bricks_args );
					foreach ( $main_bricks as $main_post ) {
						$main_post->site_id = 1;
					}
					$bricks_templates = array_merge( $bricks_templates, $main_bricks );
				}

				// Loop through each main site post
				// and add site_id to post object.
				foreach ( $main_pages as $main_post ) {
					$main_post->site_id = 1;
				}

				$pages = array_merge( $pages, $main_pages );

				restore_current_blog();
			} else {
				// Loop through each main site post
				// and add site_id to post object.
				foreach ( $pages as $page ) {
					$page->site_id = 1;
				}
				foreach ( $elementor_templates as $template ) {
					$template->site_id = 1;
				}
				foreach ( $bricks_templates as $template ) {
					$template->site_id = 1;
				}
			}
		}

		$data = array(
			'pages'               => array(),
			'elementor_templates' => array(),
			'bricks_templates'    => array(),
		);

		// Add pages with content type label
		// Only show pages from builders that don't have standalone template libraries or where templates need page assignment
		// Elementor and Bricks pages are excluded - they should use templates instead.
		$allowed_page_builders = array( 'Gutenberg', 'Divi', 'Beaver Builder', 'Breakdance' );

		if ( ! empty( $pages ) ) {
			foreach ( $pages as $post ) {
				$label = $this->get_content_type_label( $post->ID, 'page' );

				// Only include pages from allowed builders.
				if ( ! in_array( $label, $allowed_page_builders, true ) ) {
					continue;
				}

				$status_label               = $this->get_status_suffix( $post );
				$data['pages'][ $post->ID ] = array(
					'title'  => $post->post_title . $status_label,
					'label'  => $label,
					'site'   => isset( $post->site_id ) ? $post->site_id : null,
					'type'   => 'page',
					'status' => $post->post_status,
				);
			}
		}

		// Add Elementor templates with label.
		if ( ! empty( $elementor_templates ) ) {
			foreach ( $elementor_templates as $post ) {
				// Skip Elementor's Kit: it only holds the site's global colours and fonts, it is not a design.
				if ( 'kit' === get_post_meta( $post->ID, '_elementor_template_type', true ) ) {
					continue;
				}

				$status_label                             = $this->get_status_suffix( $post );
				$data['elementor_templates'][ $post->ID ] = array(
					'title'  => $post->post_title . $status_label,
					'label'  => 'Elementor Template',
					'site'   => isset( $post->site_id ) ? $post->site_id : null,
					'type'   => 'elementor_template',
					'status' => $post->post_status,
				);
			}
		}

		// Add Bricks templates with label.
		if ( ! empty( $bricks_templates ) ) {
			foreach ( $bricks_templates as $post ) {
				$status_label                          = $this->get_status_suffix( $post );
				$data['bricks_templates'][ $post->ID ] = array(
					'title'  => $post->post_title . $status_label,
					'label'  => 'Bricks Template',
					'site'   => isset( $post->site_id ) ? $post->site_id : null,
					'type'   => 'bricks_template',
					'status' => $post->post_status,
				);
			}
		}

		return $data;
	}

	/**
	 * Get user roles.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	private function get_user_role() {
		// Get current user role in multisite network using WP_User_Query.
		if ( is_multisite() ) {
			$user_query = new \WP_User_Query(
				array(
					'blog_id' => 1,
					'include' => array( get_current_user_id() ),
				)
			);

			if ( ! empty( $user_query->results ) ) {
				$roles = $user_query->results[0]->roles;

				if ( is_array( $roles ) && count( $roles ) ) {
					return $roles[0];
				}
			}
		}

		$user  = wp_get_current_user();
		$roles = $user->roles;
		$roles = array_shift( $roles );

		return $roles;
	}

	/**
	 * Get setting form database.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings() {
		$key = '_umy_wdw_templates';

		if ( is_multisite() ) {
			$settings = get_option( $key, false );
			$settings = ! $settings ? get_blog_option( 1, $key ) : $settings;
		} else {
			$settings = get_option( $key, array() );
		}

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$this->settings = $settings;

		return $settings;
	}

	/**
	 * Update settings in database
	 *
	 * @since 1.0.0
	 */
	public function update_settings() {
		// Security: Check nonce.
		if ( ! isset( $_POST['umy_wdw_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['umy_wdw_settings_nonce'] ) ), 'umy_wdw_settings' ) ) {
			return;
		}

		// Security: Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['umy_wdw_templates'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized below.
		$posted = wp_unslash( $_POST['umy_wdw_templates'] );

		if ( ! is_array( $posted ) ) {
			return;
		}

		$valid_roles = array_keys( wp_roles()->get_names() );
		$data        = array();

		foreach ( $posted as $user_role => $template ) {
			// Only real roles, each with an array of fields.
			$role_key = sanitize_key( $user_role );

			if ( ! is_array( $template ) || ! in_array( $role_key, $valid_roles, true ) ) {
				continue;
			}

			// Sanitize each field.
			$sanitized = array(
				'template'     => isset( $template['template'] ) ? absint( $template['template'] ) : 0,
				'site'         => isset( $template['site'] ) ? absint( $template['site'] ) : 0,
				'dismissible'  => isset( $template['dismissible'] ) ? 1 : 0,
				'hide_widgets' => isset( $template['hide_widgets'] ) ? 1 : 0,
			);

			$data[ $role_key ] = $sanitized;

		}

		update_option( '_umy_wdw_templates', $data );

		// Remember that settings were saved, for the success toast. Always set, also when every role is switched off.
		set_transient( 'umy_wdw_settings_saved', 1, 30 );

		// Handle multisite option.
		if ( is_multisite() && 1 === get_current_blog_id() ) {
			$hide_from_subsites = isset( $_POST['umy_wdw_hide_from_subsites'] ) ? 1 : 0;
			update_option( 'umy_wdw_hide_from_subsites', $hide_from_subsites );
		}
	}


	/**
	 * Get class instance.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Admin();
		}

		return self::$instance;
	}
}