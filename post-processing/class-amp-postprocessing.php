<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-css-utils.php' );

class AMP_Postprocessing
{
	/**
	 * Add amp attribute to html tag
	 * TODO (@amedina): Figure out how this is undone from previous steps
	 */
	public static function add_amp_attr($dom)
	{
		//error_log('Adding AMP attribute to the html tag');
		$html_tag = $dom->getElementsByTagName('html')->item(0);
		$html_tag->setAttribute('amp', '');
	}

	/**
	 * Eliminate 3rd-party JS
	 */
	public static function eliminate_3p_js($dom)
	{
		// Get rid of 3rd-party scripts
		$scripts = $dom->getElementsByTagName('script');
		$scripts_to_remove = [];
		foreach ($scripts as $script) {
			$type = $script->getAttribute('type');
			$custom_element = $script->getAttribute('custom-element');
			if ($type !== "application/ld+json" and !$custom_element) {
				$src = $script->getAttribute('src');
				if ($src !== "https://cdn.ampproject.org/v0.js") {
					array_push($scripts_to_remove, $script);
				}
			}
		}

		if ($scripts_to_remove) {
			//error_log("Removing  scripts!");
			AMP_DOM_Utils::remove_dom_nodes($scripts_to_remove);
		}
	}

	/**
	 * Eliminate external stylesheets
	 */
	public static function eliminate_ext_css($dom)
	{
		$links = $dom->getElementsByTagName('link');
		$links_to_remove = [];

		$ids = array("dashicons-css", "admin-bar-css");
		foreach ($links as $link) {
			$rel = $link->getAttribute('rel');
			$id = $link->getAttribute('id');
			if ($rel == "stylesheet" and in_array($id, $ids)) {
				array_push($links_to_remove, $link);
			}
			if ($link->hasAttribute('crossorigin')) {
				$link->removeAttribute('crossorigin');
			}
		}

		if ($links_to_remove) {
			//error_log("Removing external stylesheet links!");
			AMP_DOM_Utils::remove_dom_nodes($links_to_remove);
		}
	}

	/**
	 * Eliminate sidebars
	 */
	public static function eliminate_sidebars($dom)
	{
		$aside_secondary = $dom->getElementById('secondary');
		if ($aside_secondary) {
			//error_log("Removing secondary aside!");
			AMP_DOM_Utils::remove_dom_nodes(array($aside_secondary));
		}
	}

	/**
	 * Eliminate entry-footers
	 */
	public static function eliminate_entry_footers($dom)
	{
		$entry_footers = $dom->getElementsByTagName('footer');
		$footers_to_remove = [];
		foreach ($entry_footers as $footer) {
			$class = $footer->getAttribute('class');
			if ($class == "entry-footer") {
				array_push($footers_to_remove, $footer);
			}
		}
		if ($footers_to_remove) {
			//error_log("Removing footers!");
			AMP_DOM_Utils::remove_dom_nodes($footers_to_remove);
		}
	}

	/**
	 * Eliminate overall footer
	 */
	public static function eliminate_overall_footer($dom)
	{
		$overall_footer = $dom->getElementById('colophon');
		if ($overall_footer) {
			//error_log("Removing overall footer!");
			AMP_DOM_Utils::remove_dom_nodes(array($overall_footer));
		}
	}

	/**
	 * Eliminate post navigation
	 */
	public static function eliminate_post_navigation($dom)
	{
		$navs = $dom->getElementsByTagName('nav');
		$navs_to_remove = [];
		foreach ($navs as $nav) {
			$classes = $nav->getAttribute('class');
			if (strpos($classes, "post-navigation") !== false) {
				array_push($navs_to_remove, $nav);
			}
		}
		if ($navs_to_remove) {
			//error_log("Removing post navigation!");
			AMP_DOM_Utils::remove_dom_nodes($navs_to_remove);
		}
	}

	/**
	 * Eliminate comments section
	 */
	public static function eliminate_comments_section($dom)
	{
		$comments = $dom->getElementById('comments');
		if ($comments) {
			//error_log("Removing comments section!");
			AMP_DOM_Utils::remove_dom_nodes(array($comments));
		}
	}

	/**
	 * Set meta viewport
	 * <meta name="viewport" content="width=device-width,minimum-scale=1">
	 */
	public static function set_meta_viewport($dom)
	{
		$metatags = $dom->getElementsByTagName('meta');
		foreach ($metatags as $meta) {
			$meta_tag_name = $meta->getAttribute('name');
			if ($meta_tag_name == "viewport") {
				//error_log("Updating viewport met tag!");
				$meta->setAttribute('content', 'width=device-width,minimum-scale=1');
			}
		}
	}

	/**
	 * Eliminate non-amp-custom styles
	 */
	public static function eliminate_non_amp_custom_styles($dom)
	{
		$styles = $dom->getElementsByTagName('style');
		$styles_to_remove = [];
		foreach ($styles as $style) {
			$type = $style->getAttribute('type');
			if ($type == "text/css") {
				if (!$style->hasAttribute('amp-custom')) {
					array_push($styles_to_remove, $style);
				}
			}
		}

		if ($styles_to_remove) {
			//error_log("Removing non amp-custom style tags!");
			AMP_DOM_Utils::remove_dom_nodes($styles_to_remove);
		}
	}

	/**
	 * Minify and inline theme CSS file
	 */
	public static function inline_theme_css($dom)
	{
		//error_log("Inline theme CSS");
		// Grab stylesheet link for 2017 theme
		$stylesheet_link = $dom->getElementById('twentyseventeen-style-css');
		// Extract the the location of the file (content of the href attribute)
		$file = $stylesheet_link->getAttribute('href');
		// Minify the CSS content
		$minified_css = AMP_CSS_Utils::extract_and_minify_css_file($file);
		// Create an style element with the amp-custom attribute
		$amp_custom_style_component = $dom->createElement( 'style' );
		$amp_custom_style_component->setAttribute( 'amp-custom', '');
		// Populate it with the minified CSS
		$amp_custom_style_component->textContent = $minified_css;
		// Add the amp-custom element as a child of <head>
		$head = $dom->getElementsByTagName( 'head' )->item(0);
		$head->appendChild($amp_custom_style_component);
		// Remove the external link
		AMP_DOM_Utils::remove_dom_nodes(array($stylesheet_link));

	}

	public static function amp_custom_header_img($dom) {
		//error_log("AMPing custom-header image");
		$custom_header = $dom->getElementById( 'wp-custom-header' );
		$img = $custom_header->childNodes->item(0);
		$amp_img = $dom->createElement( 'amp-img' );
		$amp_img->setAttribute( 'src', $img->getAttribute( 'src' ));
		$amp_img->setAttribute( 'width', $img->getAttribute( 'width' ));
		$amp_img->setAttribute( 'height', $img->getAttribute( 'height' ));
		$amp_img->setAttribute( 'layout', 'responsive' );
		$amp_img->setAttribute( 'alt', $img->getAttribute( 'alt' ));
		$custom_header->appendChild($amp_img);
		AMP_DOM_Utils::remove_dom_nodes(array($img));
	}

	public static function remove_styled_svgs($dom) {
		//error_log("AMPing custom-header image");
		$svgs = $dom->getElementsByTagName( 'svg' );
		$svgs_to_remove = [];
		foreach ($svgs as $svg) {
			if ($svg->hasAttribute( 'style' )) {
				array_push($svgs_to_remove, $svg);
			}
		}
		if ($svgs_to_remove) {
			AMP_DOM_Utils::remove_dom_nodes($svgs_to_remove);
		}
	}

	public static function convert_img_tags( $dom ) {
		$img_tags = $dom->getElementsByTagName( 'img');
		foreach ($img_tags as $img) {
			$parent = $img->parentNode;
			$amp_img = $dom->createElement( 'amp-img' );
			$amp_img->setAttribute( 'alt', 'avatar' );
			$amp_img->setAttribute( 'src', $img->getAttribute( 'src' ));
			$amp_img->setAttribute( 'srcset', $img->getAttribute( 'srcset' ));
			$img_classes = $img->getAttribute( 'class');
			$amp_img->setAttribute( 'class', $img_classes);
			$amp_img->setAttribute( 'height', $img->getAttribute( 'height' ));
			$amp_img->setAttribute( 'width', $img->getAttribute( 'width' ));
			$amp_img->setAttribute( 'layout', 'responsive');

			$parent->replaceChild( $amp_img, $img );
		}
	}
}
