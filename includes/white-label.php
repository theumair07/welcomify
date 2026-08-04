<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="umy-wdw-admin-wrap">
    <!-- White Label Card -->
    <div class="umy-wdw-card">
        <!-- Header Section -->
        <div class="umy-wdw-header">
            <div class="umy-wdw-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <div class="umy-wdw-header-text">
                <h1><?php esc_html_e( 'White Label', 'welcomify' ); ?></h1>
                <p><?php esc_html_e( 'Rebrand and customize the plugin appearance to match your brand.', 'welcomify' ); ?></p>
            </div>
        </div>

        <div class="umy-wdw-card-body umy-wdw-coming-soon-body">
            <div class="umy-wdw-coming-soon">
                <div class="umy-wdw-coming-soon-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <h2 class="umy-wdw-coming-soon-title"><?php esc_html_e( 'Coming Soon', 'welcomify' ); ?></h2>
                <p class="umy-wdw-coming-soon-description">
                    <?php esc_html_e( 'White labeling options are on the way. You\'ll be able to customize the plugin name, branding, and visibility to match your business.', 'welcomify' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>