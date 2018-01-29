<?php
/**
 * Class AMP_Post_Embed_Handler
 *
 * @package AMP
 * @since ?.?
 */

/**
 * Class AMP_Post_Embed_Handler
 *
 * @todo Patch core to send embed-size message to parent when URL contains `#amp=1`. See <https://www.ampproject.org/docs/reference/components/amp-iframe#iframe-resizing>.
 */
class AMP_Post_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Register embed.
	 */
	public function register_embed() {

		// Note that oembed_dataparse filter should not be used as the response will get cached in the DB.
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 3 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10 );
	}

	/**
	 * Filter oEmbed HTML for WordPress post embeds to prepare it for AMP.
	 *
	 * @see \get_post_embed_html()
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @param array  $attr  Embed attributes, including width and height.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url, $attr ) {
		unset( $url );

		// See regex in wp_filter_oembed_result() where blockquote and iframe are presumed to exist.
		if ( ! preg_match( '#<iframe\s[^>]*class="wp-embedded-content"[^>]*?></iframe>#s', $cache ) ) {
			return $cache;
		}

		$dom    = AMP_DOM_Utils::get_dom_from_content( $cache );
		$iframe = $dom->getElementsByTagName( 'iframe' )->item( 0 );
		if ( ! $iframe ) {
			return $cache;
		}

		// Note we have to exclude the blockquote because wpautop() does not like it.
		$link = $dom->getElementsByTagName( 'a' )->item( 0 );
		$link->setAttribute( 'placeholder', '' );

		$attributes = $attr; // Initially width and height.
		foreach ( $iframe->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}
		unset( $attributes['data-secret'] );

		return AMP_HTML_Utils::build_tag(
			'amp-iframe',
			array_merge(
				$attributes,
				array(
					'src'       => strtok( $attributes['src'], '#' ), // So that `#amp=1` can be added.
					'layout'    => 'responsive',
					'resizable' => '',
					'sandbox'   => 'allow-scripts allow-top-navigation-by-user-activation', // @todo Top-navigation doesn't work because linkClickHandler() prevents it. See <https://github.com/WordPress/wordpress-develop/blob/4.9.2/src/wp-includes/js/wp-embed-template.js#L149-L170>.

				)
			),
			$dom->saveHTML( $link ) . ' <span overflow role="button" tabindex="0">' . esc_html__( 'Read more', 'amp' ) . '</span>'
		);
	}
}

