<?php
/**
 * Class AMP_WordPress_Embed_Template
 *
 * @package AMP
 */

/**
 * Class AMP_WordPress_Embed_Template
 *
 * @since 1.0
 */
class AMP_WordPress_Embed_Template {
	/**
	 * Register embed.
	 */
	public function init() {
		remove_action( 'embed_footer', 'print_embed_scripts' );
		add_action( 'embed_footer', array( $this, 'print_embed_scripts' ) );
		add_filter( 'oembed_dataparse', array( $this, 'filter_oembed_result' ), 20 );
	}

	/**
	 * Prints the JavaScript in the embed iframe header.
	 *
	 * @see \print_embed_scripts()
	 */
	public function print_embed_scripts() {
		?>
		<script type="text/javascript">
			<?php
			readfile( AMP__DIR__ . '/assets/js/wp-embed-template.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
			?>
		</script>
		<?php
	}

	/**
	 * Filters oEmbed HTML results to make them resizable.
	 *
	 * Removes the sandbox attribute so that it gets re-added by AMP_Iframe_Sanitizer.
	 *
	 * @see \wp_filter_oembed_result()
	 *
	 * @param string $html The oEmbed HTML result.
	 *
	 * @return string The filtered oEmbed result.
	 */
	public function filter_oembed_result( $html ) {
		if ( ! is_amp_endpoint() ) {
			return $html;
		}

		$html = str_ireplace( 'sandbox="allow-scripts" ', '', $html );
		$html = str_ireplace( '<iframe ', '<iframe resizable ', $html );

		return $html;
	}
}
