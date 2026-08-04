<?php
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
	 * @var object
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
     * Holds the white label page hook.
     *
     * @since 1.4.0
     * @var string
     */
    public $white_label_page_hook;

	/**
	 * Initializes the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct()
	{
		if ( ! is_admin() ) {
			return;
		}

		$this->settings_page = 'umy-wdw-settings';
		$this->settings_title = apply_filters( 'umy_wdw_settings_title', __('Welcomify - Custom Welcome Dashboard', 'welcomify') );
		$this->settings = $this->get_settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'admin_pro_badge_styles' ) );
	}

	/**
	 * Output PRO badge styles in admin head.
	 *
	 * @since 1.3.3
	 * @return void
	 */
	public function admin_pro_badge_styles() {
		?>
<style>
#adminmenu .umy-wdw-pro-badge {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6 0%, #1e3a5f 100%);
    color: #fff !important;
    font-size: 8px;
    font-weight: 600;
    padding: 1px 4px;
    border-radius: 2px;
    margin-left: 4px;
    vertical-align: middle;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.4;
}
</style>
<?php
	}

	/**
	 * Enqueue admin styles and scripts on settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook )
	{
		// Only load on our plugin pages
		$allowed_hooks = array(
			'toplevel_page_' . $this->settings_page,
			$this->white_label_page_hook
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'umy-wdw-admin-settings',
			UMY_WDW_URL . 'assets/css/admin-settings.css',
			array(),
			UMY_WDW_VER
		);

		// Enqueue notice countdown script (for success message auto-dismiss)
		wp_enqueue_script(
			'umy-wdw-admin-notice',
			UMY_WDW_URL . 'assets/js/admin-notice.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue custom dropdown script
		wp_enqueue_script(
			'umy-wdw-admin-dropdown',
			UMY_WDW_URL . 'assets/js/admin-dropdown.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue multisite handler script
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
	public function admin_init()
	{
		$this->update_settings();

		global $pagenow;

        if ( 'index.php' != $pagenow ) {
            return;
		}

		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$template_id = $settings[ $role ]['template'];
				$site_id = isset( $settings[ $role ]['site'] ) ? $settings[ $role ]['site'] : '';
				$is_multisite = is_multisite();

				// Handle multisite - switch to the correct site to check post status
				if ( ! empty( $site_id ) && $is_multisite ) {
					switch_to_blog( $site_id );
				}

				// Verify if the template exists and is accessible (not trashed or deleted)
				$post_status = get_post_status( $template_id );
				$valid_statuses = array( 'publish', 'draft', 'private' );
				$is_valid = $post_status && in_array( $post_status, $valid_statuses, true );

				if ( ! empty( $site_id ) && $is_multisite ) {
					restore_current_blog();
				}

				// If template doesn't exist or is trashed, show default WordPress welcome
				if ( ! $is_valid ) {
					return;
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
	 * Renders welcome panel.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function welcome_panel()
	{
		include UMY_WDW_DIR . 'includes/welcome-panel.php';
	}

	/**
	 * Add Dashboard Welcome to admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu()
	{	
		global $wp_roles;

		$this->roles 		= $wp_roles->get_names();
		$this->current_role = $this->get_user_role();

		if ( is_multisite() ) {
			$hide_settings = get_blog_option( 1, 'umy_wdw_hide_from_subsites' );
			
			if ( $hide_settings && get_current_blog_id() != 1 ) {
				return;
			}
		}

		if ( current_user_can( 'manage_options' ) ) {

			$title = __('Welcomify', 'welcomify');
			$cap   = 'manage_options';
			$slug  = $this->settings_page;
			$func  = array( $this, 'render_settings' );

			// Menu icon - base64 encoded SVG for WordPress.org compatibility
			$icon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 250"><path fill="#a7aaad" d="M16.416 35.431c2.914-3.588 2.903-3.559 3.147-3.875C22.428 27.85 40.163 4.912 71.5 4.522c115.494-1.436 118.892.271 128.052 4.873 50.239 25.24 44.028 51.832 44.028 124.105 0 34.054 7.458 78.842-38.178 102.756-14.231 7.457-14.856 7.839-138.901 7.213l-2.001.028c-2.413-.897-2.324-.786-5.001-.99-2.501-.971-54.917-12.786-54.993-71.007C4.358 57.64 3.125 56.196 16.416 35.431l167.092 24.081c-.044 4.561-14.695 6.084-9.755 17.66 1.582 3.709 8.187 35.831.993 52.65-1.776 4.154-.637 4.385-2.526 8.58-12.656 28.102-31.009 32.5-37.664 4.091-6.23-26.595 9.994-31.008 10.031-48.992.006-2.425.057-27.113-11.048-28.231-23.383-2.353-14.64 48.056-19.319 58.132-17.697 38.104-39.212 50.291-38.598 24.11.275-11.729 32.607-100.762-15.117-85.003-11.002 3.633-16.804 25.549-10.853 19.162 5.63-6.042 12.703 3.27 12.727 4.841.235 15.478-1.182 15.263-8.112 53.909-.682 3.804-8.338 46.497 14.233 46.172.237-.003 3.076-.044-.005 2.963 3.723.405 3.699.41 4.021.437.618.053.5-.192 1.138-.138-.357-2.242-1.377-3.168.857-3.269 25.981-1.17 34.882-39.365 40.902-39.347l1.199-.001c5.736.884 2.335 5.984 5.384 13.943 6.571 17.154 5.248 29.336 34.497 24.306 3.508-.603 26.324-12.515 37.834-47.051 12.383-37.152 9.613-75.388-5.82-76.992-2.77-.288-2.772-.009-2.978-.128-1.328-.759.05-1.549-1.182-2.254l-.839.45L16.416 35.431l167.092 24.081c-5.952.854-7.948 3.295-12.047 7.957-4.707 5.352 12.242 41.722-3.188 72.925-23.191 46.899-37.094-7.171-31.524-17.759 2.118-4.025 21.699-41.246 3.385-56.748-19.621-16.609-29.99 25.211-27.308 41.601 3.877 23.697-25.82 52.694-30.324 51.964-13.083-2.121 8.179-38.153 6.975-81.951-.749-27.235-45.588-28.132-46.006 13.004-.058 5.748 4.109-7.452 10.039-7.926 15.965-1.277-1.998 34.816-4.224 74.837.945 1.529.992 1.564.001 3.168 1.361 11.967.513 23.205 19.208 28.972 3.081-3.007.242-2.966.005-2.963-22.571.325-14.915-42.368-14.233-46.172 6.93-38.646 8.347-38.431 8.112-53.909-.024-1.571-7.097-10.883-12.727-4.841-5.951 6.387-.149-15.529 10.853-19.162 47.724-15.759 15.392 73.274 15.117 85.003-.614 26.181 20.901 13.994 38.598-24.11 4.679-10.076-4.064-60.485 19.319-58.132 11.105 1.118 11.054 25.806 11.048 28.231-.037 17.984-16.261 22.397-10.031 48.992 6.655 28.409 25.008 24.011 37.664-4.091 1.889-4.195.75-4.426 2.526-8.58 7.194-16.819.589-48.941-.993-52.65-4.94-11.576 9.711-13.099 9.755-17.66L16.416 35.431l167.931 23.631c1.232.705-.146 1.495 1.182 2.254.206.119.208-.16 2.978.128 15.433 1.604 18.203 39.84 5.82 76.992-11.51 34.536-34.326 46.448-37.834 47.051-29.249 5.03-27.926-7.152-34.497-24.306-3.049-7.959.352-13.059-5.384-13.943.539 4.385 5.991 48.794 35.824 42.032 53.812-12.197 69.634-138.588 31.911-130.208L16.416 35.431l98.997 111.808c-6.02-.018-14.921 38.177-40.902 39.347-2.234.101-1.214 1.027-.857 3.269 13.056-2.509 27.045-7.427 41.759-42.616L16.416 35.431l32.87 121.985.001 3.168c.991-1.604.944-1.639-.001-3.168L16.416 35.431Z" transform="translate(4 4)"/></svg>');

			// Add top-level menu (position 81 = below Settings)
			add_menu_page( $title, $title, $cap, $slug, $func, $icon, 81 );

			// Add "Settings" submenu (same as main menu for cleaner structure)
			add_submenu_page(
				$slug,
				__( 'Settings', 'welcomify' ),
				__( 'Settings', 'welcomify' ),
				$cap,
				$slug,
				$func
			);

			// Add "White Label" submenu with PRO badge
			$this->white_label_page_hook = add_submenu_page(
				$slug,
				__( 'White Label', 'welcomify' ),
				__( 'White Label', 'welcomify' ) . ' <span class="umy-wdw-pro-badge">PRO</span>',
				$cap,
				'umy-wdw-white-label',
				array( $this, 'render_white_label' )
			);
		}
	}

	/**
	 * Renders white label content.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function render_white_label()
	{
		include UMY_WDW_DIR . 'includes/white-label.php';
	}

	/**
	 * Renders settings content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings()
	{
		$title 			= $this->settings_title;
		$form_action 	= $this->get_form_action();
		$roles			= $this->roles;
		$current_role	= $this->current_role;
		$templates		= $this->get_templates();
		$settings		= $this->get_settings();

		include UMY_WDW_DIR . 'includes/admin-settings.php';
	}

	/**
	 * Renders template content in welcome panel using iframe embed.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_template()
	{
		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$template_id = $settings[ $role ]['template'];
				$site_id 	 = isset( $settings[ $role ]['site'] ) ? $settings[ $role ]['site'] : '';
				$dismissible = ! empty( $settings[ $role ]['dismissible'] );
				$is_multisite = is_multisite();

				// Handle multisite
				if ( ! empty( $site_id ) && $is_multisite ) {
					switch_to_blog( $site_id );
				}

				// Get the page URL
				$page_url = get_permalink( $template_id );

				if ( ! empty( $site_id ) && $is_multisite ) {
					restore_current_blog();
				}

				if ( ! $page_url ) {
					return;
				}

				// Always pass hide parameters (auto-hide enabled)
				$url_params = array(
					'umy_wdw_embed'    => '1',
					'hide_header'  => '1',
					'hide_footer'  => '1',
					'hide_title'   => '1'
				);
				
				// Add parameters to page URL
				$page_url = add_query_arg( $url_params, $page_url );

				// Add inline styles via WordPress API
				$css = '
/* Hide WordPress Dashboard title */
#wpbody-content>.wrap>h1,
#wpbody-content h1.open,
.wrap>h1:first-child {
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

/* Hide the entire dashboard widgets area to remove whitespace */
#dashboard-widgets-wrap,
#dashboard-widgets,
#dashboard-widgets .postbox-container,
#dashboard-widgets .meta-box-sortables,
#dashboard-widgets .meta-box-sortables.empty-container,
#dashboard-widgets .postbox-container .empty-container,
#normal-sortables,
#side-sortables,
#column3-sortables,
#column4-sortables {
    display: none !important;
    border: none !important;
    min-height: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    visibility: hidden !important;
    overflow: hidden !important;
}

.welcome-panel::before,
.welcome-panel::after {
    display: none !important;
}';

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
    color: #ef4444 !important;
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

				// Register and add inline style
				wp_register_style( 'umy-wdw-dashboard-embed', false, array(), UMY_WDW_VER );
				wp_enqueue_style( 'umy-wdw-dashboard-embed' );
				wp_add_inline_style( 'umy-wdw-dashboard-embed', $css );
				?>
<div class="umy-wdw-embed-container" id="umy-wdw-embed-wrapper"
    style="opacity: 0; transition: opacity 0.15s ease-in-out;">
    <iframe id="umy-wdw-page-embed" class="umy-wdw-embed-iframe" src="<?php echo esc_url( $page_url ); ?>"
        scrolling="no" title="<?php esc_attr_e( 'Dashboard Welcome Content', 'welcomify' ); ?>"></iframe>
</div>
<?php
				// Enqueue embed handler script
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
	private function get_form_action()
	{
		return admin_url( '/admin.php?page=' . $this->settings_page );
	}

	/**
	 * Check if a page was built with Elementor.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_elementor_page( $post_id )
	{
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
	public function is_bricks_page( $post_id )
	{
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
	public function is_divi_page( $post_id )
	{
		return get_post_meta( $post_id, '_et_pb_use_builder', true ) === 'on';
	}

	/**
	 * Check if a page was built with Beaver Builder.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_beaver_page( $post_id )
	{
		return get_post_meta( $post_id, '_fl_builder_enabled', true ) === '1';
	}

	/**
	 * Check if a page was built with Oxygen Builder.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_oxygen_page( $post_id )
	{
		$oxygen_data = get_post_meta( $post_id, 'ct_builder_shortcodes', true );
		return ! empty( $oxygen_data );
	}

	/**
	 * Check if a page was built with Breakdance.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_breakdance_page( $post_id )
	{
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
	public function has_gutenberg_blocks( $post_id )
	{
		$content = get_post_field( 'post_content', $post_id );
		return function_exists( 'has_blocks' ) && has_blocks( $content );
	}

	/**
	 * Get the content type label for a page.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return string
	 */
	private function get_content_type_label( $post_id, $post_type = 'page' )
	{
		// Check for Elementor template post type (only if Elementor is active)
		if ( $post_type === 'elementor_library' && \UMY_WDW_Plugin::is_elementor_active() ) {
			return 'Elementor Template';
		}

		// Check for Bricks template post type (only if Bricks is active)
		if ( $post_type === 'bricks_template' && self::is_bricks_active() ) {
			return 'Bricks Template';
		}

		// Check each page builder - only show label if builder is active
		// This prevents showing builder labels when the plugin is deactivated
		
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

		// Default: All WordPress pages without a specific page builder are Gutenberg
		return 'Gutenberg';
	}

	/**
	 * Check if Bricks Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_bricks_active()
	{
		return defined( 'BRICKS_VERSION' ) || class_exists( 'Bricks\Theme' );
	}

	/**
	 * Check if Breakdance Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_breakdance_active()
	{
		return defined( '__BREAKDANCE_VERSION' ) || class_exists( '\Breakdance\Loader' );
	}

	/**
	 * Check if Divi Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_divi_active()
	{
		return defined( 'ET_BUILDER_VERSION' ) || class_exists( 'ET_Builder_Module' );
	}

	/**
	 * Check if Beaver Builder is active.
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	public static function is_beaver_active()
	{
		return defined( 'FL_BUILDER_VERSION' ) || class_exists( 'FLBuilder' );
	}

	/**
	 * Get all WordPress pages, Elementor templates, and Bricks templates.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_templates()
	{
		// Args for all WordPress pages
		$page_args = array(
            'post_type'         => 'page',
			'posts_per_page'    => '-1',
			'post_status'		=> array( 'publish', 'private', 'draft' ),
		);

		// Args for Elementor templates (only if Elementor is active)
		$elementor_templates = array();
		if ( \UMY_WDW_Plugin::is_elementor_active() ) {
			$elementor_args = array(
				'post_type'         => 'elementor_library',
				'posts_per_page'    => '-1',
				'post_status'		=> array( 'publish', 'private', 'draft' )
			);
			$elementor_templates = get_posts( $elementor_args );
		}

		// Args for Bricks templates (only if Bricks is active)
		$bricks_templates = array();
		if ( self::is_bricks_active() ) {
			$bricks_args = array(
				'post_type'         => 'bricks_template',
				'posts_per_page'    => '-1',
				'post_status'		=> array( 'publish', 'private', 'draft' )
			);
			$bricks_templates = get_posts( $bricks_args );
		}

		$pages = get_posts( $page_args );

		// Multisite support.
		if ( is_multisite() ) {

            $blog_id = get_current_blog_id();

            if ( $blog_id != 1 ) {
                switch_to_blog(1);

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
            }
            else {
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
			'pages' => array(),
			'elementor_templates' => array(),
			'bricks_templates' => array(),
		);

		// Add pages with content type label
		// Only show pages from builders that don't have standalone template libraries or where templates need page assignment
		// Elementor and Bricks pages are excluded - they should use templates instead
		$allowed_page_builders = array( 'Gutenberg', 'Divi', 'Beaver Builder', 'Breakdance' );
		
        if ( ! empty( $pages ) && ! is_wp_error( $pages ) ){
            foreach ( $pages as $post ) {
				$label = $this->get_content_type_label( $post->ID, 'page' );
				
				// Only include pages from allowed builders
				if ( ! in_array( $label, $allowed_page_builders, true ) ) {
					continue;
				}
				
				$status_label = ( $post->post_status !== 'publish' ) ? ' [' . ucfirst( $post->post_status ) . ']' : '';
                $data['pages'][ $post->ID ] = array(
					'title'	=> $post->post_title . $status_label,
					'label' => $label,
					'site'	=> isset( $post->site_id ) ? $post->site_id : null,
					'type'  => 'page'
				);
            }
		}

		// Add Elementor templates with label
        if ( ! empty( $elementor_templates ) && ! is_wp_error( $elementor_templates ) ){
            foreach ( $elementor_templates as $post ) {
				$status_label = ( $post->post_status !== 'publish' ) ? ' [' . ucfirst( $post->post_status ) . ']' : '';
                $data['elementor_templates'][ $post->ID ] = array(
					'title'	=> $post->post_title . $status_label,
					'label' => 'Elementor Template',
					'site'	=> isset( $post->site_id ) ? $post->site_id : null,
					'type'  => 'elementor_template'
				);
            }
		}

		// Add Bricks templates with label
        if ( ! empty( $bricks_templates ) && ! is_wp_error( $bricks_templates ) ){
            foreach ( $bricks_templates as $post ) {
				$status_label = ( $post->post_status !== 'publish' ) ? ' [' . ucfirst( $post->post_status ) . ']' : '';
                $data['bricks_templates'][ $post->ID ] = array(
					'title'	=> $post->post_title . $status_label,
					'label' => 'Bricks Template',
					'site'	=> isset( $post->site_id ) ? $post->site_id : null,
					'type'  => 'bricks_template'
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
	private function get_user_role()
	{
		// Get current user role in multisite network using WP_User_Query.
        if ( is_multisite() ) {
			$user_query = new \WP_User_Query( array( 'blog_id' => 1 , 'include' => array( get_current_user_id() ) ) );
			
            if ( ! empty( $user_query->results ) ) {
				$roles = $user_query->results[0]->roles;
				
                if ( is_array( $roles ) && count( $roles ) ) {
                    return $roles[0];
                }
            }
        }

        $user   = wp_get_current_user();
        $roles  = $user->roles;
        $roles  = array_shift( $roles );

        return $roles;
	}

	/**
	 * Get setting form database.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings()
	{
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
	public function update_settings()
	{
		// Security: Check nonce
		if ( ! isset( $_POST['umy_wdw_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['umy_wdw_settings_nonce'] ) ), 'umy_wdw_settings' ) ) {
			return;
		}

		// Security: Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['umy_wdw_templates'] ) ) {
			return;
		}

		$data = array();
		$saved_pages = array();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized within loop
		foreach ( wp_unslash( $_POST['umy_wdw_templates'] ) as $user_role => $template ) {
			// Sanitize role key
			$role_key = sanitize_key( $user_role );
			
			// Properly sanitize each field
			$sanitized = array(
				'template'    => isset( $template['template'] ) ? absint( $template['template'] ) : 0,
				'site'        => isset( $template['site'] ) ? absint( $template['site'] ) : 0,
				'dismissible' => isset( $template['dismissible'] ) ? 1 : 0,
			);
			
			$data[ $role_key ] = $sanitized;
			
			// Collect page names for success message
			if ( ! empty( $sanitized['template'] ) ) {
				$page = get_post( $sanitized['template'] );
				if ( $page ) {
					$saved_pages[] = $page->post_title;
				}
			}
		}

		update_option( '_umy_wdw_templates', $data );

		// Set success message transient
		if ( ! empty( $saved_pages ) ) {
			$saved_pages = array_unique( $saved_pages );
			set_transient( 'umy_wdw_settings_saved', $saved_pages, 30 );
		}

		// Handle multisite option
		if ( is_multisite() && get_current_blog_id() == 1 ) {
			$hide_from_subsites = isset( $_POST['umy_wdw_hide_from_subsites'] ) ? 1 : 0;
			update_option( 'umy_wdw_hide_from_subsites', $hide_from_subsites );
		}
	}

	/**
	 * Delete setting form database.
	 *
	 * @since 1.0.0
	 */
	public function delete_settings()
	{
		delete_option( '_umy_wdw_templates' );
	}

	/**
	 * Get class instance.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Admin ) ) {
			self::$instance = new Admin();
		}

		return self::$instance;
	}
}