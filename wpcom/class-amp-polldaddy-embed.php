<?php
/**
 * Class WPCOM_AMP_Polldaddy_Embed
 *
 * @package AMP
 */

/**
 * Class WPCOM_AMP_Polldaddy_Embed
 */
class WPCOM_AMP_Polldaddy_Embed extends AMP_Base_Embed_Handler {

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_shortcode( 'polldaddy', array( $this, 'shortcode' ) );
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 3 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_shortcode( 'polldaddy' );
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10 );
	}

	/**
	 * Shortcode.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string Shortcode.
	 * @global WP_Embed $wp_embed
	 */
	public function shortcode( $attr ) {
		global $wp_embed;

		$url = null;
		if ( ! empty( $attr['poll'] ) ) {
			$url = 'https://polldaddy.com/poll/' . $attr['poll'] . '/';
		} elseif ( ! empty( $attr['survey'] ) ) {
			$url = 'https://polldaddy.com/s/' . $attr['survey'] . '/';
		}

		// Short-circuit in the case of the ratings embed.
		if ( ! $url ) {
			return '';
		}

		if ( ! empty( $attr['title'] ) ) {
			$output = $this->render_link( $url, $attr['title'] );
		} else {
			$output = $wp_embed->shortcode( $attr, $url );
		}

		return $output;
	}

	/**
	 * Filter oEmbed HTML for PollDaddy to for AMP output.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @param array  $attr  Shortcode attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );
		if ( false === strpos( $parsed_url['host'], 'polldaddy.com' ) ) {
			return $cache;
		}

		$output = '';

		// Poll oEmbed responses include noscript.
		if ( preg_match( '#<noscript>(.+?)</noscript>#', $cache, $matches ) ) {
			$output = $matches[1];
		}

		if ( empty( $output ) ) {
			if ( ! empty( $attr['title'] ) ) {
				$name = $attr['title'];
			} elseif ( false !== strpos( $url, 'polldaddy.com/s' ) ) {
				$name = __( 'View Survey', 'amp' );
			} else {
				$name = __( 'View Poll', 'amp' );
			}
			$output = $this->render_link( $url, $name );
		}

		return $output;
	}

	/**
	 * Render poll/survey link.
	 *
	 * @param string $url   Link URL.
	 * @param string $title Link Text.
	 * @return string Link.
	 */
	private function render_link( $url, $title ) {
		return sprintf( '<p><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></p>' );
	}
}
