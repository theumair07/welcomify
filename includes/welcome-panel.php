<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="umy-wdw-dashboard-welcome" class="umy-wdw-panel-content">
	<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
        <a class="welcome-panel-close" href="<?php echo esc_url( admin_url( 'welcome=0' ) ); ?>"><?php esc_html_e( 'Dismiss', 'welcomify' ); ?></a>
	<?php } ?>
	
	<?php $this->render_template(); ?>
</div>

<?php if ( ! current_user_can( 'edit_theme_options' ) ) { 
	$umy_wdw_inline_script = "(function($) {
		$(document).ready(function() {
			$('<div id=\"welcome-panel\" class=\"welcome-panel\"></div>').insertBefore('#dashboard-widgets-wrap').append($('#umy-wdw-dashboard-welcome'));
		});
	})(jQuery);";
	wp_add_inline_script( 'jquery', $umy_wdw_inline_script );
} ?>