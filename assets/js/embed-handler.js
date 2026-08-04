/**
 * Welcome Dashboard - Embed Iframe Handler
 * Handles iframe auto-resize and fade-in effect
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

(function () {
	'use strict';

	var iframe = document.getElementById('umy-wdw-page-embed');
	var container = document.getElementById('umy-wdw-embed-wrapper');

	if (!iframe || !container) {
		return;
	}

	var lastHeight = 0;

	/**
	 * Auto-resize iframe based on content height
	 */
	function resizeIframe() {
		try {
			var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			if (iframeDoc && iframeDoc.body) {
				var height = iframeDoc.body.scrollHeight;
				if (height > 0 && height !== lastHeight) {
					iframe.style.height = height + 'px';
					lastHeight = height;
				}
			}
		} catch (e) {
			// Cross-origin, use default height
			iframe.style.height = '500px';
		}
	}

	/**
	 * Set up MutationObserver to watch for content changes inside iframe
	 */
	function setupContentObserver() {
		try {
			var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			if (iframeDoc && iframeDoc.body) {
				var observer = new MutationObserver(function () {
					resizeIframe();
				});

				observer.observe(iframeDoc.body, {
					childList: true,
					subtree: true,
					attributes: true,
					attributeFilter: ['style', 'class']
				});

				// Also listen for transitions/animations ending
				iframeDoc.body.addEventListener('transitionend', resizeIframe);
				iframeDoc.body.addEventListener('animationend', resizeIframe);
			}
		} catch (e) {
			// Cross-origin - fall back to polling
		}
	}

	iframe.addEventListener('load', function () {
		// Fade in the entire container (hides white flash)
		container.style.opacity = '1';

		// Also fade in the dismiss button simultaneously
		var dismissBtn = document.querySelector('.welcome-panel-close');
		if (dismissBtn) {
			dismissBtn.classList.add('umy-wdw-visible');
		}

		resizeIframe();

		// Set up observer for dynamic content changes (accordion, tabs, etc.)
		setupContentObserver();

		// Resize again after short delays for dynamic content
		setTimeout(resizeIframe, 500);
		setTimeout(resizeIframe, 1500);

		// Use requestAnimationFrame for continuous height monitoring (~60fps)
		function continuousResize() {
			resizeIframe();
			requestAnimationFrame(continuousResize);
		}
		requestAnimationFrame(continuousResize);
	});

	// Resize on window resize
	window.addEventListener('resize', resizeIframe);
})();
