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
		if ( function_exists( 'soundcloud_shortcode' ) ) {
			// @todo Move this to Jetpack.
			add_shortcode( 'soundcloud', [ $this, 'shortcode' ] );
		}
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		if ( function_exists( 'soundcloud_shortcode' ) ) {
			// @todo Move this to Jetpack.
			remove_shortcode( 'soundcloud' );
		}
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 *
	 * @deprecated Core's oEmbed handler is now used instead, with embed_oembed_html filter used to convert to AMP.
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Shortcode attributes.
	 * @param string $url     URL.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches, $attr, $url ) {
		_deprecated_function( __METHOD__, '0.6' );
		return $this->render( $this->extract_params_from_iframe_src( $url ), $url );
	}

	/**
	 * Filter oEmbed HTML for SoundCloud to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		if ( false === strpos( wp_parse_url( $url, PHP_URL_HOST ), 'soundcloud.com' ) ) {
			return $cache;
		}
		return $this->parse_amp_component_from_iframe( $cache, $url );
	}

	/**
	 * Parse AMP component from iframe.
	 *
	 * @param string      $html HTML.
	 * @param string|null $url  Embed URL, for fallback purposes.
	 * @return string AMP component or empty if unable to determine SoundCloud ID.
	 */
	private function parse_amp_component_from_iframe( $html, $url ) {
		$embed = '';

		if ( preg_match( '#<iframe[^>]*?src="(?P<src>[^"]+)"#s', $html, $matches ) ) {
			$src   = html_entity_decode( $matches['src'], ENT_QUOTES );
			$query = [];
			parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );
			if ( ! empty( $query['url'] ) ) {
				$props = $this->extract_params_from_iframe_src( $query['url'] );
				if ( isset( $query['visual'] ) ) {
					$props['visual'] = $query['visual'];
				}

				if ( $url && preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+)"#s', $html, $matches ) ) {
					$props['fallback'] = sprintf(
						'<a fallback href="%s">%s</a>',
						esc_url( $url ),
						esc_html( $matches['title'] )
					);
				}

				if ( preg_match( '#<iframe[^>]*?height="(?P<height>\d+)"#s', $html, $matches ) ) {
					$props['height'] = (int) $matches['height'];
				}

				if ( preg_match( '#<iframe[^>]*?width="(?P<width>\d+)"#s', $html, $matches ) ) {
					$props['width'] = (int) $matches['width'];
				}

				$embed = $this->render( $props, $url );
			}
		}
		return $embed;
	}

	/**
	 * Render shortcode.
	 *
	 * @todo Move this to Jetpack.
	 *
	 * @param array  $attr    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Rendered shortcode.
	 */
	public function shortcode( $attr, $content = null ) {
		if ( ! function_exists( 'soundcloud_shortcode' ) ) {
			return '';
		}

		if ( isset( $attr['url'] ) ) {
			$url = $attr['url'];
		} elseif ( isset( $attr['id'] ) ) {
			$url = 'https://api.soundcloud.com/tracks/' . $attr['id'];
		} elseif ( isset( $attr[0] ) ) {
			$url = is_numeric( $attr[0] ) ? 'https://api.soundcloud.com/tracks/' . $attr[0] : $attr[0];
		} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
			$url = shortcode_new_to_old_params( $attr );
		}

		// Defer to oEmbed if an oEmbeddable URL is provided.
		if ( isset( $url ) && 'api.soundcloud.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
			global $wp_embed;
			return $wp_embed->shortcode( $attr, $url );
		}

		if ( isset( $url ) && ! isset( $attr['url'] ) ) {
			$attr['url'] = $url;
		}
		$output = soundcloud_shortcode( $attr, $content );

		return $this->parse_amp_component_from_iframe( $output, null );
	}

	/**
	 * Render embed.
	 *
	 * @param array  $args Args.
	 * @param string $url  Embed URL for fallback purposes. Optional.
	 * @return string Rendered embed.
	 * @global WP_Embed $wp_embed
	 */
	public function render( $args, $url ) {
		$args = wp_parse_args(
			$args,
			[
				'track_id'    => false,
				'playlist_id' => false,
				'height'      => null,
				'width'       => null,
				'visual'      => null,
				'fallback'    => '',
			]
		);

		$this->did_convert_elements = true;

		$attributes = [];
		if ( ! empty( $args['track_id'] ) ) {
			$attributes['data-trackid'] = $args['track_id'];
		} elseif ( ! empty( $args['playlist_id'] ) ) {
			$attributes['data-playlistid'] = $args['playlist_id'];
		} elseif ( $url ) {
			return $this->render_embed_fallback( $url );
		} else {
			return '';
		}

		if ( isset( $args['visual'] ) ) {
			$attributes['data-visual'] = rest_sanitize_boolean( $args['visual'] ) ? 'true' : 'false';
		}

		$attributes['height'] = $args['height'] ?: $this->args['height'];
		if ( $args['width'] ) {
			$attributes['width']  = $args['width'];
			$attributes['layout'] = 'responsive';
		} else {
			$attributes['layout'] = 'fixed-height';
		}

		return AMP_HTML_Utils::build_tag(
			'amp-soundcloud',
			$attributes,
			$args['fallback']
		);
	}

	/**
	 * Render embed fallback.
	 *
	 * @param string $url URL.
	 * @return string Fallback link.
	 */
	private function render_embed_fallback( $url ) {
		return AMP_HTML_Utils::build_tag(
			'a',
			[
				'href'  => esc_url_raw( $url ),
				'class' => 'amp-wp-embed-fallback',
			],
			esc_html( $url )
		);
	}

	/**
	 * Get params from Soundcloud iframe src.
	 *
	 * @param string $url URL.
	 * @return array Params extracted from URL.
	 */
	private function extract_params_from_iframe_src( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( preg_match( '#tracks/(?P<track_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'track_id' => $matches['track_id'],
			];
		}
		if ( preg_match( '#playlists/(?P<playlist_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'playlist_id' => $matches['playlist_id'],
			];
		}
		return [];
	}
}
