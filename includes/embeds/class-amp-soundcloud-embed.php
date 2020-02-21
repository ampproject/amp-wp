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
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 *
	 * @codeCoverageIgnore
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
	private function parse_amp_component_from_iframe( $html, $url = null ) {
		$props = $this->match_element_attributes( $html, 'iframe', [ 'src', 'title', 'width', 'height' ] );
		if ( ! isset( $props ) || empty( $props['src'] ) ) {
			return $html;
		}

		$src   = html_entity_decode( $props['src'], ENT_QUOTES );
		$query = [];
		parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );

		if ( empty( $query['url'] ) ) {
			return $html;
		}

		$props = array_merge(
			$props,
			$this->extract_params_from_iframe_src( $query['url'] )
		);
		if ( isset( $query['visual'] ) ) {
			$props['visual'] = $query['visual'];
		}

		if ( $url && ! empty( $props['title'] ) ) {
			$props['fallback'] = sprintf(
				'<a fallback href="%s">%s</a>',
				esc_url( $url ),
				esc_html( $props['title'] )
			);
		}

		return $this->render( $props, $url );
	}

	/**
	 * Render embed.
	 *
	 * @param array  $args Args.
	 * @param string $url  Embed URL for fallback purposes. Optional.
	 * @return string Rendered embed.
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
