/**
 * Welcomify settings page: unsaved-changes tracking.
 *
 * - Marks the sticky save bar as "dirty" while the form differs from how it was loaded.
 * - Warns before leaving the page (reload, close tab, navigate away) with unsaved changes.
 */
(function () {
    'use strict';

    var form = document.getElementById('umy-wdw-settings-form');
    var bar = document.getElementById('umy-wdw-savebar');

    if (!form || !bar) {
        return;
    }

    var status = bar.querySelector('.umy-wdw-savestatus');
    var saveBtn = bar.querySelector('button[type="submit"]');
    var text = bar.querySelector('.umy-wdw-savestatus-text');
    var dirty = false;
    var submitting = false;

    // A comparable string of every field except the security nonce / referer.
    function snapshot() {
        var parts = [];

        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name || el.name.indexOf('nonce') > -1 || el.name === '_wp_http_referer') {
                return;
            }

            if (el.type === 'checkbox' || el.type === 'radio') {
                parts.push(el.name + '=' + (el.checked ? '1' : '0'));
            } else {
                parts.push(el.name + '=' + el.value);
            }
        });

        return parts.join('&');
    }

    var initial = snapshot();

    // A role without a template gets its options dimmed (they only apply once a template is chosen).
    function syncUnset() {
        Array.prototype.forEach.call(form.querySelectorAll('.umy-wdw-role-card'), function (card) {
            var input = card.querySelector('input[type="hidden"][name$="[template]"]');
            var value = input ? input.value : '';

            card.classList.toggle('is-unset', !value || value === '0');
        });
    }

    function refresh() {
        syncUnset();
        dirty = snapshot() !== initial;
        bar.classList.toggle('is-dirty', dirty);
        text.textContent = dirty ? status.getAttribute('data-unsaved') : status.getAttribute('data-saved');

        // Nothing changed, nothing to save: the button looks inactive and does nothing.
        if (saveBtn) {
            saveBtn.setAttribute('aria-disabled', dirty ? 'false' : 'true');
        }
    }

    // The custom template dropdown updates hidden inputs without firing change events
    // and stops click propagation, so listen in the capture phase and re-check on the
    // next tick, after the dropdown's own script has run.
    ['change', 'input', 'click', 'keyup'].forEach(function (type) {
        document.addEventListener(type, function () {
            setTimeout(refresh, 0);
        }, true);
    });

    // Saving submits the form normally: don't warn about leaving in that case.
    form.addEventListener('submit', function (e) {
        // No changes: do nothing (this also covers pressing Enter inside the form).
        if (!dirty) {
            e.preventDefault();
            return;
        }

        submitting = true;
    });

    window.addEventListener('beforeunload', function (e) {
        if (!dirty || submitting) {
            return;
        }

        // Browsers show their own generic message.
        e.preventDefault();
        e.returnValue = '';
    });
})();
