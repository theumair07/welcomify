<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If Welcomify PRO is active, let it handle the Template Library
if ( defined( 'UMY_WDW_PRO_VER' ) ) {
	// Fire action for PRO to render its template library
	do_action( 'umy_wdw_pro_template_library' );
	return;
}

?>
<div class="umy-wdw-admin-wrap">
    <!-- Header Section -->
    <div class="umy-wdw-card">
        <div class="umy-wdw-header">
            <div class="umy-wdw-header-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5zM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4z" />
                </svg>
            </div>
            <div class="umy-wdw-header-text">
                <h1><?php esc_html_e( 'Template Library', 'welcomify' ); ?></h1>
                <p><?php esc_html_e( 'Import beautiful pre-designed templates with one click.', 'welcomify' ); ?></p>
            </div>
        </div>

        <div class="umy-wdw-card-body umy-wdw-template-body">
            <!-- Blur Background Overlay -->
            <div class="umy-wdw-blur-overlay"></div>

            <!-- Upgrade to Pro Modal -->
            <div class="umy-wdw-upgrade-modal">
                <!-- Top Gradient Glow -->
                <div class="umy-wdw-upgrade-glow"></div>

                <!-- Crown Icon -->
                <div class="umy-wdw-upgrade-icon">
                    <img src="<?php echo esc_url( UMY_WDW_URL . 'assets/images/upgrade-to-pro.webp' ); ?>"
                        alt="<?php esc_attr_e( 'Upgrade to Pro', 'welcomify' ); ?>">
                </div>

                <!-- Title -->
                <h2 class="umy-wdw-upgrade-title"><?php esc_html_e( 'Upgrade to Pro', 'welcomify' ); ?></h2>

                <!-- Description -->
                <p class="umy-wdw-upgrade-description">
                    <?php esc_html_e( 'One-click import for Elementor, Bricks, Divi & Gutenberg templates. Launch beautiful dashboards instantly.', 'welcomify' ); ?>
                </p>

                <!-- Glass Button -->
                <a href="https://theumair07.com/welcomify" target="_blank" rel="noopener noreferrer"
                    class="umy-wdw-upgrade-btn">
                    <?php esc_html_e( 'Upgrade to Welcomify Pro', 'welcomify' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>