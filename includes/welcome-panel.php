<?php
/**
 * Dashboard welcome panel template. Included from Admin::welcome_panel().
 *
 * @package Welcomify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$umy_wdw_can_edit_theme = current_user_can( 'edit_theme_options' );
$umy_wdw_role           = $this->current_role;
$umy_wdw_dismissible    = ! empty( $this->settings[ $umy_wdw_role ]['dismissible'] );
// Roles using Welcomify's own controls: a dismissed panel is printed hidden so Screen Options can bring it back without a reload.
$umy_wdw_dismissed = ! $umy_wdw_can_edit_theme && $this->is_dismissed( $umy_wdw_role );
?>
<div id="umy-wdw-dashboard-welcome" class="umy-wdw-panel-content<?php echo $umy_wdw_dismissed ? ' umy-wdw-is-dismissed' : ''; ?>">
	<?php if ( ! $umy_wdw_can_edit_theme && $umy_wdw_dismissible ) { ?>
		<a class="welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>"><?php esc_html_e( 'Dismiss', 'welcomify' ); ?></a>
	<?php } ?>

	<?php $this->render_template(); ?>
</div>
