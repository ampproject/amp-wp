<?php
/**
 * Class AMP_Imgur_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Imgur_Embed_Handler
 *
 * @internal
 */
class AMP_Imgur_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-imgur.
	 *
	 * @var string
	 */
	const URL_PATTERN = '#https?://(www\.)?imgur\.com/.*#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 3 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Filter oEmbed HTML for Imgur to prepare it for AMP.
	 *
	 * @param mixed  $return The oEmbed HTML.
	 * @param string $url    The attempted embed URL.
	 * @param array  $attr   Attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $return, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'], $parsed_url['path'] ) ) {
			return $return;
		}

		if ( false !== strpos( $parsed_url['host'], 'imgur.com' ) ) {
			if ( empty( $attr['height'] ) ) {
				return $return;
			}

			$attributes = wp_array_slice_assoc( $attr, [ 'width', 'height' ] );

			if ( empty( $attr['width'] ) ) {
				$attributes['layout'] = 'fixed-height';
				$attributes['width']  = 'auto';
			}

			$attributes['data-imgur-id'] = $this->get_imgur_id_from_url( $parsed_url );
			if ( false === $attributes['data-imgur-id'] ) {
				return $return;
			}

			$return = AMP_HTML_Utils::build_tag(
				'amp-imgur',
				$attributes
			);
		}
		return $return;
	}

	/**
	 * Get Imgur ID from URL.
	 *
	 * @param array $parsed_url Parsed URL.
	 * @return bool|string ID / false.
	 */
	protected function get_imgur_id_from_url( $parsed_url ) {

		if ( ! preg_match( '#^(?:/(a|gallery))?/([A-Za-z0-9]+)#', $parsed_url['path'], $matches ) ) {
			return false;
		}

		return ! empty( $matches[1] ) ? "a/{$matches[2]}" : $matches[2];
	}
}
