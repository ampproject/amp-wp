<?php
/**
 * Class DetermineHeroImages.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Transformer;

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\Transformer\PreloadHeroImage;
use DOMElement;

/**
 * Determine the images to flag as data-hero so the Optimizer can preload them.
 *
 * This transformer checks for the following images in the given order:
 * 1. Site icon
 * 2. Featured image of the page
 * 3. Block editor cover block(s)
 *
 * It then applies the data-hero attribute to the first two of these.
 *
 * @package AmpProject\AmpWP
 * @since   2.1
 * @internal
 */
final class DetermineHeroImages implements Transformer {

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document        $document DOM document to apply the
	 *                                  transformations to.
	 * @param ErrorCollection $errors   Collection of errors that are collected
	 *                                  during transformation.
	 * @return void
	 */
	public function transform( Document $document, ErrorCollection $errors ) {
		$hero_image_elements = [];

		$site_icon = $this->get_site_icon( $document );
		if ( null !== $site_icon ) {
			$hero_image_elements[] = $site_icon;
		}

		$featured_image = $this->get_featured_image( $document );
		if ( null !== $featured_image ) {
			$hero_image_elements[] = $featured_image;
		}

		if ( count( $hero_image_elements ) < 2 ) {
			$hero_image_elements = array_merge(
				$hero_image_elements,
				array_filter(
					$this->get_cover_blocks( $document )
				)
			);
		}

		$this->add_data_hero_attribute(
			array_slice(
				$hero_image_elements,
				0,
				PreloadHeroImage::DATA_HERO_MAX
			)
		);
	}

	/**
	 * Retrieve the element that represents the site icon.
	 *
	 * @param Document $document Document to retrieve the site icon from.
	 * @return DOMElement|null Element that represents the site icon, or null
	 *                         if not found.
	 */
	private function get_site_icon( Document $document ) {
		$site_icons = $document->xpath->query(
			".//a[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo-link ' ) ]//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo ' ) ]",
			$document->body
		);

		return $site_icons->item( 0 );
	}

	/**
	 * Retrieve the element that represents the featured image.
	 *
	 * @param Document $document Document to retrieve the featured image from.
	 * @return DOMElement|null Element that represents the featured image, or
	 *                         null if not found.
	 */
	private function get_featured_image( Document $document ) {
		// TODO: Add logic to detect featured image.
		return null;
	}

	/**
	 * Retrieve the element(s) that are cover blocks.
	 *
	 * @param Document $document Document to retrieve the cover blocks from.
	 * @return DOMElement[] Array of elements that are cover blocks.
	 */
	private function get_cover_blocks( Document $document ) {
		$elements = $document->xpath->query(
			".//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-block-cover ' ) ]",
			$document->body
		);

		return iterator_to_array( $elements, false );
	}

	/**
	 * Add the data-hero attribute to viable hero images.
	 *
	 * @param DOMElement[] $hero_image_elements Elements that are viable hero
	 *                                          images.
	 */
	private function add_data_hero_attribute( $hero_image_elements ) {
		foreach ( $hero_image_elements as $hero_image_element ) {
			$hero_image_element->setAttribute( Attribute::DATA_HERO, null );
		}
	}
}
