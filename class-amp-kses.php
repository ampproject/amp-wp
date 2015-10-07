<?php

class AMP_KSES {
	private static $allowed_html;
	private static $allowed_protocols;

	/**
	 * Strips blacklisted tags and attributes from content.
	 *
	 * Note: DO NOT run this on content with amp tags (see #34105-core)
	 */
	static public function strip( $content ) {
		// See https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
		$allowed_html = self::get_allowed_html();
		$allowed_protocols = self::get_allowed_protocols();
		return wp_kses( $content, $allowed_html, $allowed_protocols );
	}

	static private function get_allowed_html() {
		if ( isset( self::$allowed_html ) ) {
			return self::$allowed_html;
		}

		$blacklisted_tags = self::get_blacklisted_tags();
		$blacklisted_attributes = self::get_blacklisted_attributes();
		$whitelisted_tags = self::get_whitelisted_tags();

		$allowed_html = wp_kses_allowed_html( 'post' );

		$allowed_html = array_merge( $allowed_html, $whitelisted_tags );
		$allowed_html = array_diff_key( $allowed_html, array_fill_keys( $blacklisted_tags, false ) );
		foreach ( $allowed_html as $tag => $attributes ) {
			$allowed_html[ $tag ] = array_diff_key( $attributes, array_fill_keys( $blacklisted_attributes, false ) );
			foreach ( $attributes as $attr => $supported ) {
				// on* attributes (like onclick) are a special case
				if ( 0 === stripos( $attr, 'on' ) ) {
					unset( $allowed_html[ $tag ][ $attr ] );
					continue;
				}
			}
		}

		self::$allowed_html = $allowed_html;
		return $allowed_html;
	}

	static private function get_allowed_protocols() {
		if ( isset( self::$allowed_protocols ) ) {
			return self::$allowed_protocols;
		}

		$blacklisted_protocols = self::get_blacklisted_protocols();

		$allowed_protocols = wp_allowed_protocols();
		$allowed_protocols = array_diff_key( $allowed_protocols, array_fill_keys( $blacklisted_protocols, false ) );

		self::$allowed_protocols = $allowed_protocols;
		return $allowed_protocols;
	}

	/**
 	 * There are tags that will be converted into amp-specific versions
 	 */
	static private function get_whitelisted_tags() {
		return array(
			'img' => array(
				'alt' => true,
				'height' => true,
				'src' => true,
				'width' => true,
				'class' => true,
			),
			'audio' => array(
				'autoplay' => true,
				'controls' => true,
				'loop' => true,
				'muted' => true,
				'preload' => true,
				'src' => true,
				'class' => true,
			),
			'video' => array(
				'autoplay' => true,
				'controls' => true,
				'height' => true,
				'loop' => true,
				'muted' => true,
				'poster' => true,
				'preload' => true,
				'src' => true,
				'width' => true,
				'class' => true,
			),
			'iframe' => array (
				'src' => true,
				'sandbox' => true,
				'frameborder' => true,
				'allowfullscreen' => true,
				'allowtransparency' => true,
				'width' => true,
				'height' => true,
				'class' => true,
			),
		);
	}

	static private function get_blacklisted_protocols() {
		return array(
			'javascript',
		);
	}

	static private function get_blacklisted_tags() {
		return array(
			'script',
			'noscript',
			'style',
			'frame',
			'frameset',
			'object',
			'param',
			'applet',
			'form',
			'input',
			'button',
			'textarea',
			'select',
			'option',
			'link',
			'meta',

			// We are running kses before AMP conversion due to a kses bug (#34105-core). Technically, conversion should catch the tags and shouldn't be something we need to worry about.
			//'img',
			//'video',
			//'audio',
			//'iframe',
		);
	}

	static private function get_blacklisted_attributes() {
		return array(
			'style',
		);
	}
}

