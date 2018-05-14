<?php
/**
 * Class AMP_Gfycat_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Gfycat_Embed_Handler
 */
class AMP_Gfycat_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-gfycat.
	 *
	 * @var string
	 */
	const URL_PATTERN = '#https?://(www\.)?gfycat\.com/.*#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 3 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10 );
	}

	/**
	 * Filter oEmbed HTML for Gfycat to prepare it for AMP.
	 *
	 * @param mixed  $return The shortcode callback function to call.
	 * @param string $url    The attempted embed URL.
	 * @param array  $attr   An array of shortcode attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $return, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );
		if ( false !== strpos( $parsed_url['host'], 'gfycat.com' ) ) {
			if ( preg_match( '/width=["\']?(\d+)/', $return, $matches ) ) {
				$attr['width'] = $matches[1];
			}
			if ( preg_match( '/height=["\']?(\d+)/', $return, $matches ) ) {
				$attr['height'] = $matches[1];
			}

			if ( empty( $attr['height'] ) ) {
				return $return;
			}

			$layout = 'intrinsic';

			if ( empty( $attr['width'] ) ) {
				$layout        = 'fixed-height';
				$attr['width'] = 'auto';
			}

			$pieces = explode( '/detail/', $parsed_url['path'] );
			if ( ! isset( $pieces[1] ) ) {
				if ( ! preg_match( '/\/([A-Za-z0-9]+)/', $parsed_url['path'], $matches ) ) {
					return $return;
				}
				$data_gfyid = $matches[1];
			} else {
				$data_gfyid = $pieces[1];
			}

			$return = AMP_HTML_Utils::build_tag(
				'amp-gfycat',
				array(
					'width'      => $attr['width'],
					'height'     => $attr['height'],
					'data-gfyid' => $data_gfyid,
					'layout'     => $layout,
				)
			);
		}
		return $return;
	}
}

