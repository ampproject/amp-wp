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
		'slug'     => '',
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

		// Canonical link Node.
		$this->sanitize_assets( 'link', array( 'href' ) );

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
		$this->sanitize_assets( 'amp-img', array( 'src' ) );

		// Send the raw HTTP header for the export assets.
		if ( ! empty( $this->assets ) ) {
			header( 'X-AMP-Export-Assets: ' . implode( ',', $this->assets ) );
		}
	}

	/**
	 * Add and sanitize the export assets for the AMP Story.
	 */
	public function sanitize_assets( $name, $attritbutes ) {
		$nodes     = $this->dom->getElementsByTagName( $name );
		$num_nodes = $nodes->length;

		if ( 0 < $num_nodes ) {
			for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
				$node = $nodes->item( $i );

				if ( ! $node instanceof DOMElement ) {
					continue;
				}

				if ( ! empty( $attritbutes ) ) {
					foreach ( $attritbutes as $attritbute ) {

						// Verify we have values to update the paths with.
						$update_path = (
							! empty( $this->args['base_url'] )
							&&
							! empty( $this->args['slug'] )
						);

						// Used to generate the path with implode.
						$args = array(
							$this->args['base_url'],
							$this->args['slug'],
						);

						if ( 'link' === $name && 'href' === $attritbute ) { // @todo this is not working.
							if ( $update_path ) {
								$node->setAttribute( $attritbute, implode( '/', $args ) );
							}
						} elseif ( 'amp-story' === $name && 'publisher' === $attritbute ) {
							if ( $update_path ) {
								$parse = parse_url( $this->args['base_url'] );

								if ( ! empty( $parse['host'] ) ) {
									$node->setAttribute( $attritbute, $parse['host'] );
								}
							}
						} else {
							$asset = $node->getAttribute( $attritbute );

							// Replace the asset.
							if ( $asset && $update_path ) {
								$args[] = 'assets';
								$args[] = basename( $asset );
								$node->setAttribute( $attritbute, implode( '/', $args ) );
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
