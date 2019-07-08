<?php
/**
 * Class AMP_Story_Export_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Story_Export_Sanitizer
 *
 * Sanitizes AMP Stories during export.
 *
 * @since 1.2
 */
class AMP_Story_Export_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'base_url' => '',
	);

	/**
	 * Default assets.
	 *
	 * @var array
	 */
	protected $assets = array();

	/**
	 * Sanitize the AMP Story.
	 */
	public function sanitize() {

		// AMP Story Node.
		$attrs = array(
			'publisher-logo-src',
			'publisher',
			'poster-portrait-src',
			'poster-square-src',
			'poster-landscape-src',
		);
		$this->sanitize_assets( 'amp-story', $attrs );

		// AMP Image Nodes.
		$this->sanitize_assets( 'amp-img', array( 'src', 'srcset' ) );

		// Send the raw HTTP header for the export assets.
		if ( ! empty( $this->assets ) && ! headers_sent() ) {
			header( 'X-AMP-Export-Assets: ' . implode( ',', $this->assets ) );
		}
	}

	/**
	 * Add and sanitize the export assets for the AMP Story.
	 *
	 * @param string $name       The DOMElement name.
	 * @param array  $attributes The DOMElement attributes.
	 */
	public function sanitize_assets( $name, $attributes ) {
		$nodes     = $this->dom->getElementsByTagName( $name );
		$num_nodes = $nodes->length;

		if ( 0 < $num_nodes ) {
			for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
				$node = $nodes->item( $i );

				if ( ! $node instanceof DOMElement ) {
					continue;
				}

				if ( ! empty( $attributes ) ) {
					foreach ( $attributes as $attribute ) {

						// Verify we have a value to update the paths with.
						$update_path = ! empty( $this->args['base_url'] );

						if ( 'amp-story' === $name && 'publisher' === $attribute ) {
							if ( $update_path ) {
								$parse = wp_parse_url( $this->args['base_url'] );

								if ( ! empty( $parse['host'] ) ) {
									$node->setAttribute( $attribute, $parse['host'] );
								}
							}
						} else {
							$asset = $node->getAttribute( $attribute );

							// Replace the asset.
							if ( $asset ) {
								if ( $update_path ) {

									// Generates the path.
									$args = array(
										$this->args['base_url'],
										'assets',
										basename( $asset ),
									);

									$node->setAttribute( $attribute, implode( '/', $args ) );
								}

								// Add to assets array.
								$this->assets[] = $asset;
							}
						}
					}
				}
			}
		}
	}
}
