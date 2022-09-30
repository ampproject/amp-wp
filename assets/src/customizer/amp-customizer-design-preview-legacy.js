/* global amp_customizer_design, jQuery */

// Note: This is only used in Legacy Reader mode.
(function ($) {
	'use strict';

	// Nav bar text color.
	wp.customize('amp_customizer[header_color]', function (value) {
		value.bind(function (to) {
			$('.amp-wp-header a').css('color', to);
			$('.amp-wp-header div').css('color', to);
			$('.amp-wp-header .amp-wp-site-icon')
				.css('border-color', to)
				.css('background-color', to);
		});
	});

	// Nav bar background color.
	wp.customize('amp_customizer[header_background_color]', function (value) {
		value.bind(function (to) {
			$('html, .amp-wp-header').css('background-color', to);
			$(
				'.amp-wp-article a, .amp-wp-article a:visited, .amp-wp-footer a, .amp-wp-footer a:visited'
			).css('color', to);
			$('blockquote, .amp-wp-byline amp-img').css('border-color', to);
		});
	});

	// AMP background color scheme.
	wp.customize('amp_customizer[color_scheme]', function (value) {
		value.bind(function (to) {
			var colors = amp_customizer_design.color_schemes[to]; // eslint-disable-line no-var, camelcase

			if (!colors) {
				console.error('Selected color scheme "%s" not registered.', to); // eslint-disable-line no-console
				return;
			}

			$('body').css('background-color', colors.theme_color);
			$(
				'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title'
			).css('color', colors.text_color);
			$(
				'.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p'
			).css('color', colors.muted_text_color);
			$(
				'.wp-caption .wp-caption-text, .amp-wp-comments-link a, .amp-wp-footer'
			).css('border-color', colors.border_color);
			$(
				'.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine'
			).css('background-color', colors.border_color);
		});
	});

	// Site title.
	wp.customize('blogname', function (setting) {
		setting.bind(function (title) {
			$('.amp-wp-header .amp-site-title, .amp-wp-footer h2').text(title);
		});
	});
})(jQuery);
