<?php
/**
 * Class AMP_Reddit_Embed
 *
 * @package AMP
 */

/**
 * Class AMP_Reddit_Embed
 */
class AMP_Reddit_Embed extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-reddit.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i';

	/**
	 * Default width for Reddit embeds.
	 *
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 596; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase.

	/**
	 * Default height for Reddit embeds.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 141; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase.

	/**
	 * Script slug.
	 *
	 * @var string
	 */
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-reddit-0.1.js';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'amp-reddit', self::URL_PATTERN, array( $this, 'oembed' ) );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-reddit' );
	}

	/**
	 * Include the required AMP Reddit scripts.
	 */
	public function get_scripts() {
		return array( 'amp-reddit' => self::$script_src );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Output the Reddit amp element.
	 *
	 * @param array $args parameters used for output.
	 */
	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		return AMP_HTML_Utils::build_tag(
			'amp-reddit',
			array(
				'layout'         => 'responsive',
				'data-embedtype' => 'post',
				'width'          => $this->args['width'],
				'height'         => $this->args['height'],
				'data-src'       => $args['url'],
			)
		);
	}
}

