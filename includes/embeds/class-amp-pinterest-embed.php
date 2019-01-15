<?php
/**
 * Class AMP_Pinterest_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Pinterest_Embed_Handler
 */
class AMP_Pinterest_Embed_Handler extends AMP_Base_Embed_Handler {

	const URL_PATTERN = '#https?://(www\.)?pinterest\.com/pin/.*#i';

	/**
	 * Default width.
	 *
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 450;

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 750;

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		wp_embed_register_handler(
			'amp-pinterest',
			self::URL_PATTERN,
			array( $this, 'oembed' ),
			-1
		);
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-pinterest', -1 );
	}

	/**
	 * WordPress OEmbed rendering callback.
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Matched attributes.
	 * @param string $url     Matched URL.
	 * @param string $rawattr Raw attributes string.
	 * @return string HTML markup for rendered embed.
	 */
	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Gets the rendered embed markup.
	 *
	 * @param array $args Embed rendering arguments.
	 * @return string HTML markup for rendered embed.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'url' => false,
			)
		);

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-pinterest',
			array(
				'width'    => $this->args['width'],
				'height'   => $this->args['height'],
				'data-do'  => 'embedPin',
				'data-url' => $args['url'],
			)
		);
	}
}
