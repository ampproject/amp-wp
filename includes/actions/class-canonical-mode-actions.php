<?php

require_once(AMP__DIR__ . '/post-processing/class-amp-sanitize-tweentyseventeen-theme-plain.php');

class CanonicalModeActions {

	public static function add_actions() {
		error_log("CanonicalModeActions::add_actions()");
		if ( is_feed()) {
			return;
		}

		if( get_theme_support('amp') && is_singular() ) {
			self::add_canonical_post_actions();
		} else if (is_home()) {
			self::add_canonical_index_actions();
		}
	}

	private static function add_canonical_post_actions() {
		error_log("CanonicalModeActions::add_canonical_post_actions()");
		// Load AMP canonical actions
		require_once(AMP__DIR__ . '/includes/actions/amp-canonical-post-actions.php');
		// Load high-priority filters for canonical AMP
		require_once(AMP__DIR__ . '/includes/actions/amp-canonical-post-filters.php');
		// Template redirect to postprocessing actions
		add_action( 'template_redirect', 'CanonicalModeActions::init_post_postprocess_html');
	}

	private static function add_canonical_index_actions() {
		// TODO: Add the equivalent of amp-canonical-post-actions.php
		// and amp-canonical-post-filters.php
		// but for rendering the index page content in AMP
		//	require_once(AMP__DIR__ . '/includes/amp-canonical-index-actions.php');
		// Load high-priority filters for canonical AMP
		//	require_once(AMP__DIR__ . '/includes/amp-canonical-index-filters.php');
		add_action( 'template_redirect', 'CanonicalModeActions::init_index_postprocess_html');
	}

	public static function init_post_postprocess_html() {
		error_log("CanonicalModeActions::init_post_postprocess_html()");
		ob_start('CanonicalModeActions::postprocess_post_html');
	}

	public static function init_index_postprocess_html() {
		error_log("CanonicalModeActions::init_index_postprocess_html()");
		ob_start('CanonicalModeActions::postprocess_index_html');
	}

	public static function postprocess_index_html( $html ) {
		error_log("CanonicalModeActions::postprocess_index_html()");
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);

		// Add amp attribute to html tag
		AMP_Sanitize_TweentySeventeen_Theme::add_amp_attr($dom);

		$amp_html = $dom->saveHTML();

		return $amp_html;
	}


	/**
	 * Convert generated $html (plain 2017 Theme) to valid-AMP format
	 */
	public static function postprocess_post_html($html ) {
		error_log("CanonicalModeActions::postprocess_post_html()");
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);

		// TODO (@amedina): Define how specific theme sanitations
		// will be executed; user can select a specific one or a
		// generic one could be executed

		// Add amp attribute to html tag
		AMP_Sanitize_TweentySeventeen_Theme::add_amp_attr($dom);
		// Eliminate 3p JS
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_3p_js($dom);
		// Eliminate external stylesheets
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_ext_css($dom);
		// Eliminate sidebars
		//AMP_Sanitize_TweentySeventeen_Theme::eliminate_sidebars($dom);
		// Eliminate entry footers
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_entry_footers($dom);
		// Eliminate overall footer
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_overall_footer($dom);
		// Eliminate post navigation
		//AMP_Sanitize_TweentySeventeen_Theme::eliminate_post_navigation($dom);
		// Eliminate comments section
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_comments_section($dom);
		// Set meta viewport
		AMP_Sanitize_TweentySeventeen_Theme::set_meta_viewport($dom);
		// Eliminate non-amp-custom Stylesheets
		AMP_Sanitize_TweentySeventeen_Theme::eliminate_non_amp_custom_styles($dom);
		// Inline theme CSS
		AMP_Sanitize_TweentySeventeen_Theme::inline_theme_css($dom);
		// AMP Custom-header img
		AMP_Sanitize_TweentySeventeen_Theme::amp_custom_header_img($dom);
		// Remove styled SVGs
		AMP_Sanitize_TweentySeventeen_Theme::remove_styled_svgs($dom);
		// Save new HTML contents
		$amp_html = $dom->saveHTML();

		return $amp_html;
	}
}