/**
 * Welcome Dashboard - Settings Saved Toast
 * Closes the "Settings saved" toast on its own, or when it is dismissed.
 *
 * The toast's progress bar is a CSS animation that lasts as long as the toast stays
 * open. It pauses while the toast is hovered or focused, so the toast closes when
 * that animation ends.
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

(function () {
    'use strict';

    var toast = document.getElementById('umy-wdw-toast');

    if (!toast) {
        return;
    }

    var progress = toast.querySelector('.umy-wdw-toast-progress');
    var closeBtn = toast.querySelector('.umy-wdw-toast-close');
    var closed = false;

    function close() {
        if (closed) {
            return;
        }
        closed = true;

        toast.classList.add('is-leaving');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 220);
    }

    if (progress) {
        progress.addEventListener('animationend', close);
    } else {
        // No progress bar to time it: fall back to a plain timer.
        setTimeout(close, parseInt(toast.getAttribute('data-duration'), 10) || 6000);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', close);
    }

    // "View Dashboard" opens in another tab. Keep counting down and close as normal:
    // drop the focus it keeps, and ignore the hover pause, which would otherwise hold
    // the toast open until the pointer moves away.
    var link = toast.querySelector('.umy-wdw-toast-link');

    if (link) {
        link.addEventListener('click', function () {
            toast.classList.add('is-resumed');
            link.blur();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            close();
        }
    });
})();
