<?php
/**
 * Settings page template. Included from Admin::render_settings().
 *
 * @package Welcomify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="umy-wdw-admin-wrap">

	<?php
	// Display success message as a toast.
	$umy_wdw_saved = get_transient( 'umy_wdw_settings_saved' );
	if ( $umy_wdw_saved ) {
		delete_transient( 'umy_wdw_settings_saved' );
		$umy_wdw_dashboard_url  = admin_url( 'index.php' );
		$umy_wdw_toast_duration = 6000; // Milliseconds before the toast closes itself.
		?>
	<div class="umy-wdw-toast" id="umy-wdw-toast" role="status" aria-live="polite"
		data-duration="<?php echo esc_attr( (string) $umy_wdw_toast_duration ); ?>">
		<div class="umy-wdw-toast-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
				stroke-linejoin="round">
				<polyline points="20 6 9 17 4 12"></polyline>
			</svg>
		</div>
		<div class="umy-wdw-toast-body">
			<p class="umy-wdw-toast-title"><?php esc_html_e( 'Settings saved', 'welcomify' ); ?></p>
			<p class="umy-wdw-toast-text"><?php esc_html_e( 'Your changes are live. Open the dashboard to check them.', 'welcomify' ); ?></p>
			<?php // Named target: every click reuses the same tab instead of opening a new one. ?>
			<a href="<?php echo esc_url( $umy_wdw_dashboard_url ); ?>" target="umy-wdw-dashboard"
				class="umy-wdw-toast-link"><?php esc_html_e( 'View Dashboard', 'welcomify' ); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
					stroke-linejoin="round" aria-hidden="true">
					<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
					<polyline points="15 3 21 3 21 9"></polyline>
					<line x1="10" y1="14" x2="21" y2="3"></line>
				</svg>
				<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'welcomify' ); ?></span></a>
		</div>
		<button type="button" class="umy-wdw-toast-close"
			aria-label="<?php esc_attr_e( 'Dismiss notification', 'welcomify' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
				stroke-linejoin="round" aria-hidden="true">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</button>
		<span class="umy-wdw-toast-progress" aria-hidden="true"
			style="animation-duration: <?php echo esc_attr( (string) $umy_wdw_toast_duration ); ?>ms;"></span>
	</div>
	<?php } ?>

	<?php if ( is_multisite() && 1 !== get_current_blog_id() ) { ?>
	<div class="umy-wdw-notice umy-wdw-notice-warning">
		<div class="umy-wdw-notice-icon">
			<svg viewBox="0 0 24 24" fill="currentColor">
				<path
					d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-2h2v2zm0-4h-2V7h2v6z" />
			</svg>
		</div>
		<div class="umy-wdw-notice-content">
			<?php esc_html_e( 'Please note, changing the template in subsite will override the main settings.', 'welcomify' ); ?>
		</div>
	</div>
	<?php } ?>

	<?php // autocomplete="off": stops the browser from refilling old, unsaved values on reload, which would hide real changes from the unsaved-changes check. ?>
	<form method="post" id="umy-wdw-settings-form" autocomplete="off" action="<?php echo esc_url( $form_action ); ?>">

		<?php // Sticky bar: page title on the left, save status and button on the right. admin-settings-form.js switches the status text. ?>
		<div class="umy-wdw-savebar" id="umy-wdw-savebar">
			<div class="umy-wdw-bartitle">
				<div class="umy-wdw-header-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
						stroke-linejoin="round">
						<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
						<line x1="3" y1="9" x2="21" y2="9"></line>
						<line x1="9" y1="21" x2="9" y2="9"></line>
					</svg>
				</div>
				<div class="umy-wdw-header-text">
					<h1><?php echo esc_html( $title ); ?></h1>
					<p><?php esc_html_e( 'Customize your WordPress dashboard with any page builder.', 'welcomify' ); ?></p>
				</div>
			</div>
			<a class="umy-wdw-link" href="<?php echo esc_url( admin_url( 'admin.php?page=umy-wdw-start' ) ); ?>"><?php esc_html_e( 'New here? Getting started', 'welcomify' ); ?></a>
			<span class="umy-wdw-savestatus" role="status" aria-live="polite"
				data-saved="<?php esc_attr_e( 'All changes saved', 'welcomify' ); ?>"
				data-unsaved="<?php esc_attr_e( 'You have unsaved changes', 'welcomify' ); ?>">
				<span class="umy-wdw-savestatus-dot" aria-hidden="true"></span>
				<span class="umy-wdw-savestatus-text"><?php esc_html_e( 'All changes saved', 'welcomify' ); ?></span>
			</span>
			<button type="submit" class="umy-wdw-btn umy-wdw-btn-primary" aria-disabled="true">
				<?php esc_html_e( 'Save Changes', 'welcomify' ); ?>
			</button>
		</div>

		<div class="umy-wdw-role-grid">
						<?php
						foreach ( $roles as $umy_wdw_role => $umy_wdw_role_title ) {
							$umy_wdw_has_template = ! empty( $settings[ $umy_wdw_role ]['template'] );
							// On by default: roles saved before this option existed keep the clean dashboard they had.
							$umy_wdw_hide_widgets = ! isset( $settings[ $umy_wdw_role ]['hide_widgets'] ) || ! empty( $settings[ $umy_wdw_role ]['hide_widgets'] );
							// A draft is only shown to users who may edit it. Roles that can't (e.g. Author, Contributor,
							// custom roles) get a note on their card; admin-dropdown.js keeps it current.
							$umy_wdw_role_object = get_role( $umy_wdw_role );
							$umy_wdw_sees_drafts = $umy_wdw_role_object && $umy_wdw_role_object->has_cap( 'edit_others_pages' ) && $umy_wdw_role_object->has_cap( 'edit_others_posts' );
							$umy_wdw_is_draft    = false;
							if ( $umy_wdw_has_template && ! $umy_wdw_sees_drafts ) {
								foreach ( array( 'pages', 'elementor_templates', 'bricks_templates' ) as $umy_wdw_type ) {
									if ( isset( $templates[ $umy_wdw_type ][ $settings[ $umy_wdw_role ]['template'] ]['status'] ) ) {
										$umy_wdw_is_draft = 'draft' === $templates[ $umy_wdw_type ][ $settings[ $umy_wdw_role ]['template'] ]['status'];
										break;
									}
								}
							}
							?>
			<div class="umy-wdw-card umy-wdw-role-card<?php echo $umy_wdw_has_template ? '' : ' is-unset'; ?><?php echo $umy_wdw_is_draft ? ' is-draft' : ''; ?>"
				data-role="<?php echo esc_attr( $umy_wdw_role ); ?>"
				data-sees-drafts="<?php echo $umy_wdw_sees_drafts ? '1' : '0'; ?>">
				<div class="umy-wdw-card-header">
					<span class="umy-wdw-role-badge"><?php echo esc_html( $umy_wdw_role_title ); ?></span>
				</div>
				<div class="umy-wdw-card-body umy-wdw-role-card-body">
					<div class="umy-wdw-field">
						<span class="umy-wdw-field-label" id="umy-wdw-tpl-label-<?php echo esc_attr( $umy_wdw_role ); ?>"><?php esc_html_e( 'Page / Template', 'welcomify' ); ?></span>
								<div class="umy-wdw-select-wrap">
									<?php
									$umy_wdw_selected_id    = isset( $settings[ $umy_wdw_role ]['template'] ) ? $settings[ $umy_wdw_role ]['template'] : '';
									$umy_wdw_selected_title = '';
									$umy_wdw_selected_label = '';
									$umy_wdw_selected_site  = isset( $settings[ $umy_wdw_role ]['site'] ) ? $settings[ $umy_wdw_role ]['site'] : '';

									// Find selected template info.
									if ( $umy_wdw_selected_id ) {
										foreach ( array( 'pages', 'elementor_templates', 'bricks_templates' ) as $umy_wdw_type ) {
											if ( isset( $templates[ $umy_wdw_type ][ $umy_wdw_selected_id ] ) ) {
												$umy_wdw_selected_title = $templates[ $umy_wdw_type ][ $umy_wdw_selected_id ]['title'];
												$umy_wdw_selected_label = $templates[ $umy_wdw_type ][ $umy_wdw_selected_id ]['label'];
												break;
											}
										}
									}
									?>
									<div class="umy-wdw-dropdown">
										<!-- Hidden inputs for form submission -->
										<input type="hidden" name="umy_wdw_templates[<?php echo esc_attr( $umy_wdw_role ); ?>][template]" value="<?php echo esc_attr( $umy_wdw_selected_id ); ?>">
										<?php if ( is_multisite() ) { ?>
										<input type="hidden" name="umy_wdw_templates[<?php echo esc_attr( $umy_wdw_role ); ?>][site]" value="<?php echo esc_attr( $umy_wdw_selected_site ); ?>">
										<?php } ?>
										
										<!-- Trigger Button -->
										<button type="button" class="umy-wdw-dropdown-trigger" role="combobox"
											aria-haspopup="listbox" aria-expanded="false"
											aria-labelledby="umy-wdw-tpl-label-<?php echo esc_attr( $umy_wdw_role ); ?>"
											aria-controls="umy-wdw-tpl-list-<?php echo esc_attr( $umy_wdw_role ); ?>"
											<?php echo $umy_wdw_is_draft ? 'aria-describedby="umy-wdw-draft-note-' . esc_attr( $umy_wdw_role ) . '"' : ''; ?>>
											<span class="umy-wdw-dropdown-selected">
												<?php if ( $umy_wdw_selected_id && $umy_wdw_selected_title ) : ?>
													<span class="umy-wdw-dropdown-selected-title"><?php echo esc_html( $umy_wdw_selected_title ); ?></span>
													<span class="umy-wdw-dropdown-selected-label label-<?php echo esc_attr( sanitize_title( $umy_wdw_selected_label ) ); ?>"><?php echo esc_html( $umy_wdw_selected_label ); ?></span>
												<?php else : ?>
													<span class="umy-wdw-dropdown-placeholder"><?php esc_html_e( '— Select —', 'welcomify' ); ?></span>
												<?php endif; ?>
											</span>
											<svg class="umy-wdw-dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<polyline points="6 9 12 15 18 9"></polyline>
											</svg>
										</button>
										
										<!-- Dropdown Panel -->
										<div class="umy-wdw-dropdown-panel" role="listbox"
											id="umy-wdw-tpl-list-<?php echo esc_attr( $umy_wdw_role ); ?>"
											aria-labelledby="umy-wdw-tpl-label-<?php echo esc_attr( $umy_wdw_role ); ?>">
											<!-- Options -->
											<div class="umy-wdw-dropdown-options">
												<!-- Empty Option -->
												<div class="umy-wdw-dropdown-option <?php echo empty( $umy_wdw_selected_id ) ? 'is-selected' : ''; ?>" 
													data-value="" 
													data-site=""
													data-status=""
													role="option"
													aria-selected="<?php echo empty( $umy_wdw_selected_id ) ? 'true' : 'false'; ?>">
													<span class="umy-wdw-dropdown-option-content">
														<span class="umy-wdw-dropdown-option-title"><?php esc_html_e( '— Select —', 'welcomify' ); ?></span>
													</span>
													<svg class="umy-wdw-dropdown-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
														<polyline points="20 6 9 17 4 12"></polyline>
													</svg>
												</div>
												
												<?php if ( ! empty( $templates['pages'] ) ) : ?>
												<!-- Pages Group -->
												<div class="umy-wdw-dropdown-group" role="group" aria-label="<?php esc_attr_e( 'Pages', 'welcomify' ); ?>">
													<div class="umy-wdw-dropdown-group-label" aria-hidden="true"><?php esc_html_e( 'Pages', 'welcomify' ); ?></div>
													<?php foreach ( $templates['pages'] as $umy_wdw_id => $umy_wdw_tpl ) : ?>
													<div class="umy-wdw-dropdown-option <?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'is-selected' : ''; ?>" 
														data-value="<?php echo esc_attr( $umy_wdw_id ); ?>"
														data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
														data-status="<?php echo esc_attr( $umy_wdw_tpl['status'] ?? '' ); ?>"
														role="option"
														aria-selected="<?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'true' : 'false'; ?>">
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
												<div class="umy-wdw-dropdown-group" role="group" aria-label="<?php esc_attr_e( 'Elementor Templates', 'welcomify' ); ?>">
													<div class="umy-wdw-dropdown-group-label" aria-hidden="true"><?php esc_html_e( 'Elementor Templates', 'welcomify' ); ?></div>
													<?php foreach ( $templates['elementor_templates'] as $umy_wdw_id => $umy_wdw_tpl ) : ?>
													<div class="umy-wdw-dropdown-option <?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'is-selected' : ''; ?>" 
														data-value="<?php echo esc_attr( $umy_wdw_id ); ?>"
														data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
														data-status="<?php echo esc_attr( $umy_wdw_tpl['status'] ?? '' ); ?>"
														role="option"
														aria-selected="<?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'true' : 'false'; ?>">
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
												<div class="umy-wdw-dropdown-group" role="group" aria-label="<?php esc_attr_e( 'Bricks Templates', 'welcomify' ); ?>">
													<div class="umy-wdw-dropdown-group-label" aria-hidden="true"><?php esc_html_e( 'Bricks Templates', 'welcomify' ); ?></div>
													<?php foreach ( $templates['bricks_templates'] as $umy_wdw_id => $umy_wdw_tpl ) : ?>
													<div class="umy-wdw-dropdown-option <?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'is-selected' : ''; ?>" 
														data-value="<?php echo esc_attr( $umy_wdw_id ); ?>"
														data-site="<?php echo esc_attr( $umy_wdw_tpl['site'] ?? '' ); ?>"
														data-status="<?php echo esc_attr( $umy_wdw_tpl['status'] ?? '' ); ?>"
														role="option"
														aria-selected="<?php echo ( (int) $umy_wdw_selected_id === (int) $umy_wdw_id ) ? 'true' : 'false'; ?>">
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
						<p class="umy-wdw-field-hint"><?php esc_html_e( 'Pick a template to turn on the options below.', 'welcomify' ); ?></p>
						<p class="umy-wdw-draft-note" id="umy-wdw-draft-note-<?php echo esc_attr( $umy_wdw_role ); ?>" role="note"><?php esc_html_e( 'This is a draft, so users with this role will not see it. Set it to Private instead.', 'welcomify' ); ?></p>
					</div>

					<div class="umy-wdw-option">
						<label class="umy-wdw-toggle">
							<input type="checkbox" id="umy-wdw-dismissible-<?php echo esc_attr( $umy_wdw_role ); ?>"
								name="umy_wdw_templates[<?php echo esc_attr( $umy_wdw_role ); ?>][dismissible]"
								value="1" role="switch"
								<?php checked( ! empty( $settings[ $umy_wdw_role ]['dismissible'] ) ); ?> />
							<span class="umy-wdw-toggle-slider" aria-hidden="true"></span>
						</label>
						<div class="umy-wdw-option-text">
							<label class="umy-wdw-option-title" for="umy-wdw-dismissible-<?php echo esc_attr( $umy_wdw_role ); ?>"><?php esc_html_e( 'Dismissible', 'welcomify' ); ?></label>
							<span><?php esc_html_e( 'Let users close the welcome panel.', 'welcomify' ); ?></span>
						</div>
					</div>

					<div class="umy-wdw-option">
						<label class="umy-wdw-toggle">
							<input type="checkbox" id="umy-wdw-hide-widgets-<?php echo esc_attr( $umy_wdw_role ); ?>"
								name="umy_wdw_templates[<?php echo esc_attr( $umy_wdw_role ); ?>][hide_widgets]"
								value="1" role="switch"
								<?php checked( $umy_wdw_hide_widgets ); ?> />
							<span class="umy-wdw-toggle-slider" aria-hidden="true"></span>
						</label>
						<div class="umy-wdw-option-text">
							<label class="umy-wdw-option-title" for="umy-wdw-hide-widgets-<?php echo esc_attr( $umy_wdw_role ); ?>"><?php esc_html_e( 'Hide other widgets', 'welcomify' ); ?></label>
							<span><?php esc_html_e( 'Remove the other dashboard widgets for this role, even if the user dismisses the panel.', 'welcomify' ); ?></span>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>

			<?php if ( is_multisite() && 1 === get_current_blog_id() ) { ?>
			<div class="umy-wdw-multisite-option">
				<label class="umy-wdw-checkbox-label">
					<input type="checkbox" value="1" name="umy_wdw_hide_from_subsites"
						<?php checked( get_option( 'umy_wdw_hide_from_subsites' ), true ); ?> />
					<span class="umy-wdw-checkbox-box"></span>
					<?php esc_html_e( 'Hide settings from network subsites', 'welcomify' ); ?>
				</label>
			</div>
			<?php } ?>

		<?php wp_nonce_field( 'umy_wdw_settings', 'umy_wdw_settings_nonce' ); ?>
	</form>
</div>