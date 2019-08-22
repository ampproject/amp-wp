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
		'base_url'      => '',
		'canonical_url' => '',
	];

	/**
	 * Default assets.
	 *
	 * @var string[]
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

		$xpath           = new DOMXPath( $this->dom );
		$schema_org_meta = $xpath->query( '//head/script[@type="application/ld+json"]' )->item( 0 );

		if ( $schema_org_meta && $schema_org_meta->firstChild instanceof DOMText ) {
			$metadata = json_decode( $schema_org_meta->firstChild->nodeValue );

			// Adds the image to the assets array.
			if ( isset( $metadata->image->url ) ) {
				$image_url = $metadata->image->url;

				if ( $image_url && ! in_array( $image_url, $this->assets, true ) ) {
					$this->assets[] = $image_url;
				}
			}

			// Adds the logo image to the assets array.
			if ( isset( $metadata->publisher->logo->url ) ) {
				$logo_url = $metadata->publisher->logo->url;

				if ( $logo_url && ! in_array( $logo_url, $this->assets, true ) ) {
					$this->assets[] = $logo_url;
				}
			}

			if ( $this->args['base_url'] && $this->args['canonical_url'] ) {

				// Replace the image URL.
				if ( isset( $image_url ) ) {
					$args = [
						$this->args['canonical_url'],
						'assets',
						AMP_Story_Post_Type::export_image_basename( $image_url ),
					];

					$metadata->image->url = implode( '/', $args );
				}

				// Replace the logo URL.
				if ( isset( $logo_url ) ) {
					$args = [
						$this->args['canonical_url'],
						'assets',
						AMP_Story_Post_Type::export_image_basename( $logo_url ),
					];

					$metadata->publisher->logo->url = implode( '/', $args );
				}

				// Replace the Canonical URL.
				$metadata->mainEntityOfPage = $this->args['canonical_url']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}

			$schema_org_meta->firstChild->nodeValue = wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		}

		// Add or update Canonical URL in the document head.
		if ( $this->args['base_url'] && $this->args['canonical_url'] ) {
			$canonical_link = $xpath->query( '/html/head/link[ @rel = "canonical" ]' )->item( 0 );
			if ( $canonical_link instanceof DOMElement ) {
				$canonical_link->setAttribute( 'href', $this->args['canonical_url'] );
			} else {
				$canonical_link = AMP_DOM_Utils::create_node(
					$this->dom,
					'link',
					[
						'rel'  => 'canonical',
						'href' => $this->args['canonical_url'],
					]
				);
				$this->dom->getElementsByTagName( 'head' )->item( 0 )->appendChild( $canonical_link );
			}
		}

		// Add the export assets as an HTML comment.
		$encoded = wp_json_encode( $this->assets, JSON_PRETTY_PRINT );
		$encoded = str_replace( '--', '\u002d\u002d', $encoded ); // Prevent "--" in strings from breaking out of HTML comments.
		$comment = $this->dom->createComment( 'AMP_EXPORT_ASSETS:' . $encoded . "\n" );
		$this->dom->documentElement->appendChild( $comment );
	}

	/**
	 * Add and sanitize the export assets for the AMP Story.
	 *
	 * @param string   $name       The DOMElement name.
	 * @param string[] $attributes The DOMElement attributes.
	 */
	public function sanitize_assets( $name, array $attributes ) {
		$nodes     = $this->dom->getElementsByTagName( $name );
		$num_nodes = $nodes->length;

		// Verify we have a value to update the paths with.
		$update_path = ! empty( $this->args['canonical_url'] ) ? $this->args['canonical_url'] : false;

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
					AMP_Story_Post_Type::export_image_basename( $asset ),
				];
				return implode( '/', $args );
			}
			return false;
		};

		if ( 0 < $num_nodes ) {
			foreach ( $nodes as $node ) {
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
