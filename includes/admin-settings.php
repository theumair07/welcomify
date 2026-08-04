<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="umy-wdw-admin-wrap">

    <?php 
	// Display success message
	$umy_wdw_saved_pages = get_transient( 'umy_wdw_settings_saved' );
	if ( $umy_wdw_saved_pages && is_array( $umy_wdw_saved_pages ) ) {
		delete_transient( 'umy_wdw_settings_saved' );
		$umy_wdw_page_names = esc_html( implode( ', ', $umy_wdw_saved_pages ) );
		$umy_wdw_dashboard_url = admin_url( 'index.php' );
		?>
    <div class="umy-wdw-notice umy-wdw-notice-success" id="umy-wdw-success-notice">
        <div class="umy-wdw-notice-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-1.25 17.292l-4.5-4.364 1.857-1.858 2.643 2.506 5.643-5.784 1.857 1.857-7.5 7.643z" />
            </svg>
        </div>
        <div class="umy-wdw-notice-content">
            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $umy_wdw_page_names is escaped with esc_html() on line 8 ?>
            <strong><?php echo $umy_wdw_page_names; ?></strong>
            <?php esc_html_e('successfully set as custom Dashboard.', 'welcomify'); ?>
        </div>
        <a href="<?php echo esc_url( $umy_wdw_dashboard_url ); ?>" class="umy-wdw-btn umy-wdw-btn-success"
            id="umy-wdw-view-dashboard-btn">
            <?php esc_html_e('View Dashboard', 'welcomify'); ?> <span id="umy-wdw-countdown">(5)</span>
        </a>
    </div>
    <?php } ?>

    <?php if ( is_multisite() && get_current_blog_id() != 1 ) { ?>
    <div class="umy-wdw-notice umy-wdw-notice-warning">
        <div class="umy-wdw-notice-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
        </div>
        <div class="umy-wdw-notice-content">
            <?php esc_html_e('Please note, changing the template in subsite will override the main settings.', 'welcomify'); ?>
        </div>
    </div>
    <?php } ?>

    <!-- Settings Card with Header Inside -->
    <div class="umy-wdw-card">
        <!-- Header Section -->
        <div class="umy-wdw-header">
            <div class="umy-wdw-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
            </div>
            <div class="umy-wdw-header-text">
                <h1><?php echo esc_html( $title ); ?></h1>
                <p><?php esc_html_e('Customize your WordPress dashboard with any page builder.', 'welcomify'); ?>
                </p>
            </div>
        </div>

        <form method="post" id="umy-wdw-settings-form" action="<?php echo esc_url( $form_action ); ?>">
            <div class="umy-wdw-card-body">
                <table class="umy-wdw-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('User Role', 'welcomify'); ?></th>
                            <th><?php esc_html_e('Page / Template', 'welcomify'); ?></th>
                            <th><?php esc_html_e('Dismissible', 'welcomify'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variable, not global ?>
                        <?php foreach ( $roles as $role => $umy_wdw_role_title ) { ?>
                        <tr>
                            <td data-label="<?php esc_attr_e('User Role', 'welcomify'); ?>">
                                <span class="umy-wdw-role-badge"><?php echo esc_html( $umy_wdw_role_title ); ?></span>
                            </td>
                            <td data-label="<?php esc_attr_e('Page / Template', 'welcomify'); ?>">
                                <div class="umy-wdw-select-wrap">
                                    <?php
                                    $umy_wdw_selected_id = isset( $settings[$role]['template'] ) ? $settings[$role]['template'] : '';
                                    $umy_wdw_selected_title = '';
                                    $umy_wdw_selected_label = '';
                                    $umy_wdw_selected_site = isset( $settings[$role]['site'] ) ? $settings[$role]['site'] : '';
                                    
                                    // Find selected template info
                                    if ( $umy_wdw_selected_id ) {
                                        foreach ( array('pages', 'elementor_templates', 'bricks_templates') as $umy_wdw_type ) {
                                            if ( isset( $templates[$umy_wdw_type][$umy_wdw_selected_id] ) ) {
                                                $umy_wdw_selected_title = $templates[$umy_wdw_type][$umy_wdw_selected_id]['title'];
                                                $umy_wdw_selected_label = $templates[$umy_wdw_type][$umy_wdw_selected_id]['label'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="umy-wdw-dropdown">
                                        <!-- Hidden inputs for form submission -->
                                        <input type="hidden" name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][template]" value="<?php echo esc_attr( $umy_wdw_selected_id ); ?>">
                                        <?php if ( is_multisite() ) { ?>
                                        <input type="hidden" name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][site]" value="<?php echo esc_attr( $umy_wdw_selected_site ); ?>">
                                        <?php } ?>
                                        
                                        <!-- Trigger Button -->
                                        <button type="button" class="umy-wdw-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
                                            <span class="umy-wdw-dropdown-selected">
                                                <?php if ( $umy_wdw_selected_id && $umy_wdw_selected_title ) : ?>
                                                    <span class="umy-wdw-dropdown-selected-title"><?php echo esc_html( $umy_wdw_selected_title ); ?></span>
                                                    <span class="umy-wdw-dropdown-selected-label label-<?php echo esc_attr( sanitize_title( $umy_wdw_selected_label ) ); ?>"><?php echo esc_html( $umy_wdw_selected_label ); ?></span>
                                                <?php else : ?>
                                                    <span class="umy-wdw-dropdown-placeholder"><?php esc_html_e('— Select —', 'welcomify'); ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <svg class="umy-wdw-dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown Panel -->
                                        <div class="umy-wdw-dropdown-panel" role="listbox">
                                            <!-- Options -->
                                            <div class="umy-wdw-dropdown-options">
                                                <!-- Empty Option -->
                                                <div class="umy-wdw-dropdown-option <?php echo empty($umy_wdw_selected_id) ? 'is-selected' : ''; ?>" 
                                                     data-value="" 
                                                     data-site=""
                                                     role="option"
                                                     aria-selected="<?php echo empty($umy_wdw_selected_id) ? 'true' : 'false'; ?>">
                                                    <span class="umy-wdw-dropdown-option-content">
                                                        <span class="umy-wdw-dropdown-option-title"><?php esc_html_e('— Select —', 'welcomify'); ?></span>
                                                    </span>
                                                    <svg class="umy-wdw-dropdown-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </div>
                                                
                                                <?php if ( ! empty( $templates['pages'] ) ) : ?>
                                                <!-- Pages Group -->
                                                <div class="umy-wdw-dropdown-group">
                                                    <div class="umy-wdw-dropdown-group-label"><?php esc_html_e('Pages', 'welcomify'); ?></div>
                                                    <?php foreach ( $templates['pages'] as $id => $umy_wdw_tpl ) : ?>
                                                    <div class="umy-wdw-dropdown-option <?php echo ($umy_wdw_selected_id == $id) ? 'is-selected' : ''; ?>" 
                                                         data-value="<?php echo esc_attr( $id ); ?>"
                                                         data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
                                                         role="option"
                                                         aria-selected="<?php echo ($umy_wdw_selected_id == $id) ? 'true' : 'false'; ?>">
                                                        <span class="umy-wdw-dropdown-option-content">
                                                            <span class="umy-wdw-dropdown-option-title"><?php echo esc_html( $umy_wdw_tpl['title'] ); ?></span>
                                                            <span class="umy-wdw-dropdown-option-label label-<?php echo esc_attr( sanitize_title( $umy_wdw_tpl['label'] ) ); ?>"><?php echo esc_html( $umy_wdw_tpl['label'] ); ?></span>
                                                        </span>
                                                        <svg class="umy-wdw-dropdown-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if ( ! empty( $templates['elementor_templates'] ) ) : ?>
                                                <!-- Elementor Templates Group -->
                                                <div class="umy-wdw-dropdown-group">
                                                    <div class="umy-wdw-dropdown-group-label"><?php esc_html_e('Elementor Templates', 'welcomify'); ?></div>
                                                    <?php foreach ( $templates['elementor_templates'] as $id => $umy_wdw_tpl ) : ?>
                                                    <div class="umy-wdw-dropdown-option <?php echo ($umy_wdw_selected_id == $id) ? 'is-selected' : ''; ?>" 
                                                         data-value="<?php echo esc_attr( $id ); ?>"
                                                         data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
                                                         role="option"
                                                         aria-selected="<?php echo ($umy_wdw_selected_id == $id) ? 'true' : 'false'; ?>">
                                                        <span class="umy-wdw-dropdown-option-content">
                                                            <span class="umy-wdw-dropdown-option-title"><?php echo esc_html( $umy_wdw_tpl['title'] ); ?></span>
                                                            <span class="umy-wdw-dropdown-option-label label-<?php echo esc_attr( sanitize_title( $umy_wdw_tpl['label'] ) ); ?>"><?php echo esc_html( $umy_wdw_tpl['label'] ); ?></span>
                                                        </span>
                                                        <svg class="umy-wdw-dropdown-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if ( ! empty( $templates['bricks_templates'] ) ) : ?>
                                                <!-- Bricks Templates Group -->
                                                <div class="umy-wdw-dropdown-group">
                                                    <div class="umy-wdw-dropdown-group-label"><?php esc_html_e('Bricks Templates', 'welcomify'); ?></div>
                                                    <?php foreach ( $templates['bricks_templates'] as $id => $umy_wdw_tpl ) : ?>
                                                    <div class="umy-wdw-dropdown-option <?php echo ($umy_wdw_selected_id == $id) ? 'is-selected' : ''; ?>" 
                                                         data-value="<?php echo esc_attr( $id ); ?>"
                                                         data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
                                                         role="option"
                                                         aria-selected="<?php echo ($umy_wdw_selected_id == $id) ? 'true' : 'false'; ?>">
                                                        <span class="umy-wdw-dropdown-option-content">
                                                            <span class="umy-wdw-dropdown-option-title"><?php echo esc_html( $umy_wdw_tpl['title'] ); ?></span>
                                                            <span class="umy-wdw-dropdown-option-label label-<?php echo esc_attr( sanitize_title( $umy_wdw_tpl['label'] ) ); ?>"><?php echo esc_html( $umy_wdw_tpl['label'] ); ?></span>
                                                        </span>
                                                        <svg class="umy-wdw-dropdown-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="<?php esc_attr_e('Dismissible', 'welcomify'); ?>">
                                <label class="umy-wdw-toggle"
                                    aria-label="<?php /* translators: %s: User role name */ printf( esc_attr__( 'Toggle dismissible for %s', 'welcomify' ), esc_attr( $umy_wdw_role_title ) ); ?>">
                                    <input type="checkbox"
                                        name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][dismissible]"
                                        value="1" role="switch"
                                        aria-checked="<?php echo ! empty( $settings[$role]['dismissible'] ) ? 'true' : 'false'; ?>"
                                        <?php checked( ! empty( $settings[$role]['dismissible'] ) ); ?> />
                                    <span class="umy-wdw-toggle-slider" aria-hidden="true"></span>
                                </label>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ( is_multisite() && get_current_blog_id() == 1 ) { ?>
            <div class="umy-wdw-multisite-option">
                <label class="umy-wdw-checkbox-label">
                    <input type="checkbox" value="1" name="umy_wdw_hide_from_subsites"
                        <?php checked( get_option( 'umy_wdw_hide_from_subsites' ), true ); ?> />
                    <span class="umy-wdw-checkbox-box"></span>
                    <?php esc_html_e('Hide settings from network subsites', 'welcomify'); ?>
                </label>
            </div>
            <?php } ?>

            <?php wp_nonce_field( 'umy_wdw_settings', 'umy_wdw_settings_nonce' ); ?>

            <div class="umy-wdw-card-footer">
                <button type="submit" class="umy-wdw-btn umy-wdw-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php esc_html_e('Save Changes', 'welcomify'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="umy-wdw-info-box">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-6h2v6zm0-8h-2V7h2v2z" />
        </svg>
        <span><?php esc_html_e('Create a page with any WordPress page builder, then select it here to display as the welcome panel on your dashboard.', 'welcomify'); ?></span>
    </div>
</div>