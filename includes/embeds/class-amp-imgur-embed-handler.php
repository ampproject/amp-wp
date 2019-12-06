<?php
/**
 * Class AMP_Imgur_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Imgur_Embed_Handler
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
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {

			// Before 4.9 the embedding Imgur is not working properly, register embed for that case.
			wp_embed_register_handler( 'amp-imgur', self::URL_PATTERN, [ $this, 'oembed' ], -1 );
		} else {
			add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 3 );
		}
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {
			wp_embed_unregister_handler( 'amp-imgur', -1 );
		} else {
			remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
		}
	}

	/**
	 * Oembed.
	 *
	 * @param array  $matches Matches.
	 * @param array  $attr Attributes.
	 * @param string $url URL.
	 * @return string Embed.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( [ 'url' => $url ] );
	}

	/**
	 * Render embed.
	 *
	 * @param array $args Args.
	 * @return string Embed.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'url' => false,
			]
		);

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$this->did_convert_elements = true;

		$id = $this->get_imgur_id_from_url( $args['url'] );
		if ( false === $id ) {
			return '';
		}
		return AMP_HTML_Utils::build_tag(
			'amp-imgur',
			[
				'width'         => $this->args['width'],
				'height'        => $this->args['height'],
				'data-imgur-id' => $id,
			]
		);
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
		if ( false !== strpos( $parsed_url['host'], 'imgur.com' ) ) {
			if ( preg_match( '/width=["\']?(\d+)/', $return, $matches ) ) {
				$attr['width'] = $matches[1];
			}
			if ( preg_match( '/height=["\']?(\d+)/', $return, $matches ) ) {
				$attr['height'] = $matches[1];
			}

			if ( empty( $attr['height'] ) ) {
				return $return;
			}

			$attributes = wp_array_slice_assoc( $attr, [ 'width', 'height' ] );

			if ( empty( $attr['width'] ) ) {
				$attributes['layout'] = 'fixed-height';
				$attributes['width']  = 'auto';
			}

			$attributes['data-imgur-id'] = $this->get_imgur_id_from_url( $url );
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
	 * @param string $url URL.
	 * @return bool|string ID / false.
	 */
	protected function get_imgur_id_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		$pieces     = explode( '/gallery/', $parsed_url['path'] );
		if ( ! isset( $pieces[1] ) ) {
			if ( ! preg_match( '/\/([A-Za-z0-9]+)/', $parsed_url['path'], $matches ) ) {
				return false;
			}
			return $matches[1];
		}

		return $pieces[1];
	}
}
