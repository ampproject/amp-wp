<?php
require_once(AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-html-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-string-utils.php');

require_once(AMP__DIR__ . '/includes/content/class-amp-content.php');

require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-blacklist-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-img-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-video-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-iframe-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-audio-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-style-sanitizer.php');

require_once(AMP__DIR__ . '/includes/embeds/class-amp-twitter-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-youtube-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-gallery-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-instagram-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-vine-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-facebook-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-dailymotion-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-soundcloud-embed.php');

require_once(AMP__DIR__ . '/includes/content/amp-content-generator.php');
require_once(AMP__DIR__ . '/post-processing/class-amp-sanitize-tweentyseventeen-theme-plain.php');

class AMPCanonicalPostActions {

	public static function the_content_filter($content) {
		if (isset($GLOBALS['amp_content'])) {
			return $GLOBALS['amp_content']->get_amp_content();
		} else {
			return $content;
		}
	}

	public static function deregister_scripts() {
		wp_deregister_script( 'wp_embed');
	}

	public static function add_boilerplate_css() {
		?>
        <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
		<?php
	}

	public static function add_scripts() {

		$amp_runtime_script = 'https://cdn.ampproject.org/v0.js';

		// Always include AMP form & analytics, as almost every template includes
		// a search form and is tracking page views
		$scripts = array_merge(
			array('amp-form' => 'https://cdn.ampproject.org/v0/amp-form-0.1.js'),
			array('amp-analytics' => 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js'),
			$GLOBALS['amp_content']->get_amp_scripts()
		);

		foreach ( $scripts as $element => $script) : ?>
            <script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
		<?php endforeach; ?>
        <script src="<?php echo esc_url( $amp_runtime_script ); ?>" async></script>
		<?php
	}

	public static function add_canonical() {
        //error_log("CANONICAL: Adding canonical link")
		?>
        <link rel="canonical" href="<?php echo esc_url( get_site_url() ); ?>" />
		<?php
	}

	/**
	 * Convert generated $html (plain 2017 Theme) to valid-AMP format
	 */
	public static function postprocess_post_html($html ) {
		error_log("AMPCanonicalPostActions::postprocess_post_html()");
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

// Generate the AMP post content early on
// Runs the filters for the_content, but skips our filters below
$post = get_post();
$GLOBALS['amp_content'] = AMPContentGenerator::amp_canonical_retrieve_content( $post );

// The filter for the_content hook was already invoked,
// attempt to remove all filters
remove_all_filters('the_content');

/* @TODO (amedina, mo): check this:
    The wp_head hook is theme-dependent which means that it is up to the author of each
    WordPress theme to include it. It may not be available on all themes, so you should
    take this into account when using it.
*/
add_action( 'wp_head', 'AMPCanonicalPostActions::add_scripts' );
add_action( 'wp_head', 'AMPCanonicalPostActions::add_boilerplate_css' );
add_action( 'wp_footer', 'AMPCanonicalPostActions::deregister_scripts' );

// Add the filter that replaces the content, and ensure
// that no content filters run afterwards
add_filter( 'the_content', 'AMPCanonicalPostActions::the_content_filter', PHP_INT_MAX);

// TODO (@amedina) Get the canonical URL [Check!]
if (!is_singular()) {
	add_action( 'wp_head', 'AMPCanonicalPostActions::add_canonical');
}
