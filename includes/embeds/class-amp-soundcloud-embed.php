<?php
/**
 * Class AMP_SoundCloud_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_SoundCloud_Embed_Handler
 *
 * @since 0.5
 */
class AMP_SoundCloud_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 200;

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_shortcode( 'soundcloud', array( $this, 'shortcode' ) );
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_shortcode( 'soundcloud' );
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10 );
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 *
	 * @deprecated Core's oEmbed handler is now used instead, with embed_oembed_html filter used to convert to AMP.
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Shortcode attribues.
	 * @param string $url     URL.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches, $attr, $url ) {
		_deprecated_function( __METHOD__, '0.6' );
		unset( $matches, $attr );
		$track_id = $this->get_track_id_from_url( $url );
		return $this->render( compact( 'track_id' ) );
	}

	/**
	 * Filter oEmbed HTML for SoundCloud to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( false === strpos( $parsed_url['host'], 'soundcloud.com' ) ) {
			return $cache;
		}
		return $this->parse_amp_component_from_iframe( $cache );
	}

	/**
	 * Parse AMP component from iframe.
	 *
	 * @param string $html HTML.
	 * @return string AMP component or empty if unable to determine SoundCloud ID.
	 */
	private function parse_amp_component_from_iframe( $html ) {
		$embed = '';
		if ( preg_match( '#<iframe.+?src="(?P<url>.+?)".*>#', $html, $matches ) ) {
			$src   = html_entity_decode( $matches['url'], ENT_QUOTES );
			$query = array();
			parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );
			if ( ! empty( $query['url'] ) ) {
				$embed = $this->render(
					array(
						'track_id' => $this->get_track_id_from_url( $query['url'] ),
					)
				);
			}
		}
		return $embed;
	}

	/**
	 * Render shortcode.
	 *
	 * @param array  $attr    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Rendered shortcode.
	 */
	public function shortcode( $attr, $content = null ) {
		$output = '';
		if ( function_exists( 'soundcloud_shortcode' ) ) {
			if ( empty( $attr['url'] ) && ! empty( $attr['id'] ) ) {
				$attr['url'] = 'https://api.soundcloud.com/tracks/' . intval( $attr['id'] );
			}
			$output = soundcloud_shortcode( $attr, $content );
			$output = $this->parse_amp_component_from_iframe( $output );
		} else {
			$url = null;
			if ( isset( $attr['id'] ) ) {
				$url = 'https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F' . intval( $attr['id'] );
			}

			if ( isset( $attr['url'] ) ) {
				$url = $attr['url'];
			} elseif ( isset( $attr[0] ) ) {
				$url = $attr[0];
			} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
				$url = shortcode_new_to_old_params( $attr );
			}

			if ( $url ) {
				$output = $this->render_embed_fallback( $url );
			}
		}
		return $output;
	}

	/**
	 * Render embed.
	 *
	 * @param array $args Args.
	 * @return string Rendered embed.
	 * @global WP_Embed $wp_embed
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'track_id' => false,
				'url'      => null,
			)
		);

		if ( empty( $args['track_id'] ) ) {
			return $this->render_embed_fallback( $args['url'] );
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-soundcloud',
			array(
				'data-trackid' => $args['track_id'],
				'layout'       => 'fixed-height',
				'height'       => $this->args['height'],
			)
		);
	}

	/**
	 * Render embed fallback.
	 *
	 * @param string $url URL.
	 * @returns string
	 */
	private function render_embed_fallback( $url ) {
		return AMP_HTML_Utils::build_tag(
			'a',
			array(
				'href'  => esc_url( $url ),
				'class' => 'amp-wp-embed-fallback',
			),
			esc_html( $url )
		);
	}

	/**
	 * Get track_id from URL.
	 *
	 * @param string $url URL.
	 *
	 * @return string Track ID or empty string if none matched.
	 */
	private function get_track_id_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( ! preg_match( '#tracks/(?P<track_id>[^/]+)#', $parsed_url['path'], $matches ) ) {
			return '';
		}
		return $matches['track_id'];
	}
}
