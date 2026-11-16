<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$umy_wdw_logos        = UMY_WDW_URL . 'assets/images/builders/';
$umy_wdw_settings_url = admin_url( 'admin.php?page=' . $this->settings_page );
$umy_wdw_new_page_url = admin_url( 'post-new.php?post_type=page' );

// A builder's "open templates" link is only shown while that builder is active on this site.
$umy_wdw_bricks_url    = self::is_bricks_active() ? admin_url( 'edit.php?post_type=bricks_template' ) : '';
$umy_wdw_elementor_url = \UMY_WDW_Plugin::is_elementor_active() ? admin_url( 'edit.php?post_type=elementor_library' ) : '';

$umy_wdw_external_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
?>
<div class="umy-wdw-admin-wrap umy-wdw-start-page">

    <?php // Same compact bar as the Settings page (sticks to the top while scrolling). ?>
    <div class="umy-wdw-savebar">
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
                <h1><?php esc_html_e( 'Getting started with Welcomify', 'welcomify' ); ?></h1>
                <p><?php esc_html_e( 'Put your own design on the WordPress dashboard.', 'welcomify' ); ?></p>
            </div>
        </div>
    </div>

    <div class="umy-wdw-card umy-wdw-guide">
        <h2><?php esc_html_e( 'Build your welcome design', 'welcomify' ); ?></h2>
        <p class="umy-wdw-guide-intro"><?php esc_html_e( 'What you can do depends on the editor you use:', 'welcomify' ); ?></p>

        <ul class="umy-wdw-guide-list">
            <li>
                <span class="umy-wdw-guide-icon"><img src="<?php echo esc_url( $umy_wdw_logos . 'bricks.webp' ); ?>" width="28" height="28" alt=""></span>
                <span class="umy-wdw-guide-name">
                    <strong><?php esc_html_e( 'Bricks Builder', 'welcomify' ); ?></strong>
                    <small><?php echo wp_kses( __( 'Create a <b>template</b> in Bricks Builder.', 'welcomify' ), array( 'b' => array() ) ); ?></small>
                </span>
                <?php if ( $umy_wdw_bricks_url ) { ?>
                <a class="umy-wdw-link" href="<?php echo esc_url( $umy_wdw_bricks_url ); ?>"><?php esc_html_e( 'Open Bricks templates', 'welcomify' ); ?> <?php echo $umy_wdw_external_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static inline SVG. ?></a>
                <?php } ?>
            </li>
            <li>
                <span class="umy-wdw-guide-icon"><img src="<?php echo esc_url( $umy_wdw_logos . 'elementor.webp' ); ?>" width="28" height="28" alt=""></span>
                <span class="umy-wdw-guide-name">
                    <strong><?php esc_html_e( 'Elementor', 'welcomify' ); ?></strong>
                    <small><?php echo wp_kses( __( 'Create a <b>template</b> in Elementor.', 'welcomify' ), array( 'b' => array() ) ); ?></small>
                </span>
                <?php if ( $umy_wdw_elementor_url ) { ?>
                <a class="umy-wdw-link" href="<?php echo esc_url( $umy_wdw_elementor_url ); ?>"><?php esc_html_e( 'Open Elementor templates', 'welcomify' ); ?> <?php echo $umy_wdw_external_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static inline SVG. ?></a>
                <?php } ?>
            </li>
        </ul>

        <ul class="umy-wdw-guide-list">
            <li>
                <span class="umy-wdw-guide-icon umy-wdw-stack" aria-hidden="true">
                    <img src="<?php echo esc_url( $umy_wdw_logos . 'gutenberg.webp' ); ?>" width="30" height="30" title="Gutenberg" alt="">
                    <img src="<?php echo esc_url( $umy_wdw_logos . 'kadence.webp' ); ?>" width="30" height="30" title="Kadence" alt="">
                    <img src="<?php echo esc_url( $umy_wdw_logos . 'beaver-builder.webp' ); ?>" width="30" height="30" title="Beaver Builder" alt="">
                    <span class="umy-wdw-stack-more" title="Divi">+</span>
                </span>
                <span class="umy-wdw-guide-name">
                    <strong><?php esc_html_e( 'Any other editor', 'welcomify' ); ?></strong>
                    <small><?php echo wp_kses( __( 'Gutenberg, Kadence, Divi, Beaver Builder and others: create a <b>page</b> and keep it <b>Private</b>.', 'welcomify' ), array( 'b' => array() ) ); ?></small>
                </span>
                <a class="umy-wdw-link" href="<?php echo esc_url( $umy_wdw_new_page_url ); ?>"><?php esc_html_e( 'Add new page', 'welcomify' ); ?> <?php echo $umy_wdw_external_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static inline SVG. ?></a>
            </li>
        </ul>

        <div class="umy-wdw-notice umy-wdw-notice-warning umy-wdw-tip">
            <div class="umy-wdw-notice-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
            </div>
            <div class="umy-wdw-notice-content"><?php echo wp_kses( __( 'Set the page <strong>Visibility</strong> to <strong>Private</strong> (in the page settings), so visitors cannot open it directly.', 'welcomify' ), array( 'strong' => array() ) ); ?></div>
        </div>
    </div>

    <?php // Bar fixed to the bottom of the screen, full width (CSS: .umy-wdw-next). ?>
    <div class="umy-wdw-next">
        <div class="umy-wdw-next-text">
            <h2><?php esc_html_e( 'Then choose it in Settings', 'welcomify' ); ?></h2>
            <p><?php echo wp_kses( __( 'Your page or template is ready? Open Settings. There you can pick a different card or template for <strong>every user role</strong>. Then check your dashboard.', 'welcomify' ), array( 'strong' => array() ) ); ?></p>
        </div>
        <div class="umy-wdw-next-actions">
            <a class="umy-wdw-btn umy-wdw-btn-primary" href="<?php echo esc_url( $umy_wdw_settings_url ); ?>">
                <?php esc_html_e( 'Open Settings', 'welcomify' ); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
        </div>
    </div>

</div>
