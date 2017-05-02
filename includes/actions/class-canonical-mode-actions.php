<?php

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
		error_log("CanonicalModeActions::add_canonical_index_actions()");
		require_once(AMP__DIR__ . '/includes/actions/amp-canonical-index-actions.php');
		// Load high-priority filters for canonical AMP
		require_once(AMP__DIR__ . '/includes/actions/amp-canonical-post-filters.php');
		add_action( 'template_redirect', 'CanonicalModeActions::init_index_postprocess_html');
	}

	public static function init_post_postprocess_html() {
		error_log("CanonicalModeActions::init_post_postprocess_html()");
		ob_start('AMPCanonicalPostActions::postprocess_post_html');
	}

	public static function init_index_postprocess_html() {
		error_log("CanonicalModeActions::init_index_postprocess_html()");
		ob_start('AMPCanonicalIndexActions::postprocess_index_html');
	}

}