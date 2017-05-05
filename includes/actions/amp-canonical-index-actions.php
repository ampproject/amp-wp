<?php

require_once(AMP__DIR__ . '/includes/content/amp-content-generator.php');
require_once(AMP__DIR__ . '/post-processing/class-amp-postprocessing.php');

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

	public static function deregister_scripts() {
		wp_deregister_script( 'wp_embed');
	}

	public static function add_boilerplate_css() {
		?>
		<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
		<?php
	}

	public static function add_general_scripts() {
		$amp_runtime_script = 'https://cdn.ampproject.org/v0.js';

		// Always include AMP form & analytics, as almost every template includes
		// a search form and is tracking page views
		$scripts = array_merge(
			array('amp-form' => 'https://cdn.ampproject.org/v0/amp-form-0.1.js'),
			array('amp-analytics' => 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js')
		);

		foreach ( $scripts as $element => $script) : ?>
			<script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
		<?php endforeach; ?>
		<script src="<?php echo esc_url( $amp_runtime_script ); ?>" async></script>
		<?php
	}

	public static function add_scripts($dom) {

		$scripts = array();
		// Flatten the arrays of scripts
		foreach ( self::$amp_posts as $amp_post) {
			$amp_scripts = $amp_post->get_amp_scripts();
			// Use an associative array for dedupping scripts
			foreach ($amp_scripts as $element => $amp_script) {
				$scripts[ $element ] = $amp_script;
			}
		}

		$head = $dom->getElementsByTagName('head')->item(0);
		foreach ( $scripts as $element => $script) {
			$custom_script = $dom->createElement( 'script');
			$custom_script->setAttribute('async', '');
			$custom_script->setAttribute('custom-element', esc_attr( $element ));
			$custom_script->setAttribute('src', esc_url( $script ));
			$head->appendChild($custom_script);
		}
	}

	public static function add_canonical($dom) {

		$head = $dom->getElementsByTagName('head')->item(0);
		$link = $dom->createElement( 'link');
		$link->setAttribute('rel', 'canonical');
		$link->setAttribute('href', esc_url( get_site_url() ) );
		$head->appendChild($link);
	}
	
	public static function postprocess_index_html( $html ) {
		error_log("AMPCanonicalIndexActions::postprocess_index_html()");

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);

		self::add_scripts($dom);
		self::add_canonical($dom);

		// Add amp attribute to html tag
		AMP_Postprocessing::add_amp_attr($dom);
		// Eliminate 3p JS
		AMP_Postprocessing::eliminate_3p_js($dom);
		// Eliminate external stylesheets
		AMP_Postprocessing::eliminate_ext_css($dom);
		// Set meta viewport
		AMP_Postprocessing::set_meta_viewport($dom);
		// Eliminate non-amp-custom Stylesheets
		AMP_Postprocessing::eliminate_non_amp_custom_styles($dom);
		// Inline theme CSS
		AMP_Postprocessing::inline_theme_css($dom);
		// AMP Custom-header img
		AMP_Postprocessing::amp_custom_header_img($dom);
		// Remove styled SVGs
		AMP_Postprocessing::remove_styled_svgs($dom);
		// Save new HTML contents
		$amp_html = $dom->saveHTML();

		return $amp_html;
	}
}

AMPCanonicalIndexActions::init();
add_action( 'wp_head', 'AMPCanonicalIndexActions::add_general_scripts' );
add_action( 'wp_head', 'AMPCanonicalIndexActions::add_boilerplate_css' );
add_action( 'wp_footer', 'AMPCanonicalIndexActions::deregister_scripts' );
