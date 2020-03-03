<?php
/**
 * Class AMP_Tiktok_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Tiktok_Embed_Handler
 */
class AMP_Tiktok_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 3 );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Filter oEmbed HTML for Tiktok to prepare it for AMP.
	 *
	 * @param mixed  $html The oEmbed HTML.
	 * @param string $url The attempted embed URL.
	 * @param array  $attr Attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $html, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );

		if ( false === strpos( $parsed_url['host'], 'tiktok.com' ) ) {
			return $html;
		}

		$video_id = $this->get_video_id( $html );

		if ( ! $video_id ) {
			return AMP_HTML_Utils::build_tag(
				'a',
				[
					'href'  => esc_url_raw( $url ),
					'class' => 'amp-wp-embed-fallback',
				],
				esc_html( $url )
			);
		}

		if ( $video_id && preg_match( '#<blockquote(?:\s.*?)?>(?P<content>.*?)</blockquote>#s', $html, $matches ) ) {
			$content = str_replace( '<section>', '<section placeholder>', $matches['content'] );

			$iframe = AMP_HTML_Utils::build_tag(
				'amp-iframe',
				[
					'width'   => $attr['width'],
					'height'  => $attr['height'],

					/*
					 * A `lang` query parameter is added to the URL via JS. This can't be determined here so it is not
					 * added. Whether it alters the embed in any way or not has not been determined.
					 */
					'src'     => 'https://www.tiktok.com/embed/v2/' . $video_id,
					'sandbox' => 'allow-scripts allow-same-origin',
				],
				$content
			);

			// On the non-amp page the embed is wrapped with a <blockquote>, so the same is done here.
			$html = "<blockquote>{$iframe}</blockquote>";
		}

		return $html;
	}

	/**
	 * Parse video ID from HTML.
	 *
	 * @param string $html HTML.
	 * @return string|false Video ID if found, else false.
	 */
	protected function get_video_id( $html ) {
		if ( preg_match( '#data-video-id="(\d+?)"#', $html, $matches ) ) {
			return $matches[1];
		}

		return false;
	}
}
