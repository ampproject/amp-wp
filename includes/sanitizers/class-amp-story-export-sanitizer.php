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
 * @since 1.2.1
 */
class AMP_Story_Export_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'base_url' => '',
	];

	/**
	 * Default assets.
	 *
	 * @var array
	 */
	protected $assets = [];

	/**
	 * Sanitize the AMP Story.
	 */
	public function sanitize() {

		// AMP Story Node.
		$this->sanitize_assets(
			'amp-story',
			[
				'publisher-logo-src',
				'publisher',
				'poster-portrait-src',
				'poster-square-src',
				'poster-landscape-src',
			]
		);

		// AMP Image Nodes.
		$this->sanitize_assets(
			'amp-img',
			[
				'src',
				'srcset',
			]
		);

		// AMP Video Nodes.
		$this->sanitize_assets(
			'amp-video',
			[
				'src',
				'poster',
			]
		);

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

		// Verify we have a value to update the paths with.
		$update_path = ! empty( $this->args['base_url'] ) ? $this->args['base_url'] : false;

		/**
		 * Generates the new asset path.
		 *
		 * @param string $asset The original URL.
		 *
		 * @return bool|string Returns false when $update_path is false, else the new URL.
		 */
		$get_asset_path = function( $asset ) use ( $update_path ) {
			if ( $asset && $update_path ) {
				$args = [
					$update_path,
					'assets',
					basename( $asset ),
				];
				return implode( '/', $args );
			}
			return false;
		};

		if ( 0 < $num_nodes ) {
			for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
				$node = $nodes->item( $i );

				if ( ! $node instanceof DOMElement ) {
					continue;
				}

				if ( ! empty( $attributes ) ) {
					foreach ( $attributes as $attribute ) {
						if ( 'amp-story' === $name && 'publisher' === $attribute ) {
							if ( $update_path ) {
								$parse = wp_parse_url( $update_path );

								if ( ! empty( $parse['host'] ) ) {
									$node->setAttribute( $attribute, $parse['host'] );
								}
							}
						} else {
							$asset = $node->getAttribute( $attribute );

							// Replace the asset.
							if ( $asset ) {
								if ( 'srcset' === $attribute ) {
									$images = array_filter(
										array_map(
											static function ( $srcset_part ) {
												// Remove descriptors for width and pixel density.
												return preg_replace( '/\s.*$/', '', trim( $srcset_part ) );
											},
											preg_split( '/\s*,\s*/', $asset )
										)
									);

									if ( ! empty( $images ) ) {
										foreach ( $images as $image ) {
											if ( $update_path ) {
												$asset = str_replace( $image, $get_asset_path( $image ), $asset );
											}

											// Add to assets array.
											$this->assets[] = $image;
										}

										// Reset the attribute after replacing all the images.
										$node->setAttribute( $attribute, $asset );
									}
								} else {
									if ( $update_path ) {
										$node->setAttribute( $attribute, $get_asset_path( $asset ) );
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
}
