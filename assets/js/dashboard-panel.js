/**
 * Welcomify dashboard panel controls.
 *
 * Used for roles without the edit_theme_options capability. WordPress hides
 * its own welcome panel, Welcome checkbox and dismiss from them, so Welcomify
 * moves its panel into place and handles dismissing / restoring it.
 *
 * A dismissed panel stays in the page, hidden, so it can be shown again from
 * Screen Options without reloading the page.
 */
(function ($) {
    'use strict';

    var config = window.umyWdwDashboard || {};

    function save(visible) {
        return $.post(config.ajaxUrl, {
            action: 'umy_wdw_dismiss',
            nonce: config.nonce,
            visible: visible ? 1 : 0
        });
    }

    function hidePanel() {
        $('#welcome-panel').slideUp(150, function () {
            // WordPress' own "hidden" class keeps it hidden, and lets the widgets/title come back.
            $(this).addClass('hidden').css('display', '');
        });
        $('#umy-wdw-dashboard-welcome').addClass('umy-wdw-is-dismissed');
    }

    function showPanel() {
        $('#umy-wdw-dashboard-welcome').removeClass('umy-wdw-is-dismissed');
        $('#welcome-panel').hide().removeClass('hidden').slideDown(150);
    }

    // The iframe is not loaded while the panel is dismissed, and its access token is
    // short-lived, so it is loaded with the fresh URL the server returns.
    function loadIframe(url) {
        var $iframe = $('#umy-wdw-page-embed');

        url = url || $iframe.attr('data-src');

        if (url) {
            $iframe.attr('src', url);
        }
    }

    // Move the panel above the dashboard widgets, like WordPress' own welcome panel.
    $(function () {
        var $panel = $('#umy-wdw-dashboard-welcome');

        if (!$panel.length) {
            return;
        }

        var $wrap = $('<div id="welcome-panel" class="welcome-panel"></div>')
            .insertBefore('#dashboard-widgets-wrap')
            .append($panel);

        if ($panel.hasClass('umy-wdw-is-dismissed')) {
            $wrap.addClass('hidden');
        }
    });

    // Dismiss link inside the panel.
    $(document).on('click', '#umy-wdw-dashboard-welcome .welcome-panel-close', function (e) {
        e.preventDefault();
        save(false);
        hidePanel();
        $('#umy-wdw-panel-hide').prop('checked', false);
    });

    // "Welcome" checkbox in Screen Options.
    $(document).on('change', '#umy-wdw-panel-hide', function () {
        var checkbox = this;

        if (checkbox.checked) {
            // Show the panel right away, then load the template with the fresh URL from the server.
            showPanel();
            save(true)
                .done(function (response) {
                    loadIframe(response && response.success && response.data ? response.data.url : '');
                })
                .fail(function () {
                    loadIframe('');
                });
        } else {
            save(false);
            hidePanel();
        }
    });
})(jQuery);
