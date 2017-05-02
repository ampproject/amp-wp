<?php

require_once(AMP__DIR__ . '/includes/content/amp-content-generator.php');
require_once(AMP__DIR__ . '/post-processing/class-amp-sanitize-tweentyseventeen-theme-plain.php');

class AMPCanonicalIndexActions
{
	static $amp_posts;
	static $current;
	static $is_processing;

	public static function init() {
		error_log("AMPCanonicalIndexActions::init()");
		self:$amp_posts = array();
		add_action( 'the_post', array( __CLASS__, 'prepare_content' ));
		add_action( 'the_content', array( __CLASS__, 'the_content_filter'), 99999 );
	}

	public static function the_content_filter($content ) {
		error_log("AMPCanonicalIndexActions::the_content_filter()");
		// Avoid infinite loops when calling amp_canonical_retrieve_content
		if (self::$is_processing ) {
			return $content;
		}
		//return self::$current->get_amp_content();
		$post = get_post();
		return self::$amp_posts[ $post->ID ]->get_amp_content();
	}

	public static function prepare_content( $post ) {
		error_log("AMPCanonicalIndexActions::prepare_content()");
		if ( !isset( self::$amp_posts[ $post->ID ] ) ) {
			self::$is_processing = true;
			self::$amp_posts[ $post->ID ] = AMPContentGenerator::amp_canonical_retrieve_content( $post );
			self::$is_processing = false;
		}

		self::$current = self::$amp_posts[ $post->ID ];
	}


	public static function postprocess_index_html( $html ) {
		error_log("AMPCanonicalIndexActions::postprocess_index_html()");

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

AMPCanonicalIndexActions::init();
