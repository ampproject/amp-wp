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
 * 1. Custom logo
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
	 * XPath query to find the existing hero images (elements with data/hero attribute).
	 *
	 * @var string
	 */
	const EXISTING_HERO_IMAGES_XPATH_QUERY = './/*[@data-hero]';

	/**
	 * XPath query to find the custom logo.
	 *
	 * @var string
	 */
	const CUSTOM_LOGO_XPATH_QUERY = ".//a[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo-link ' ) ]//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo ' ) ][ not( @data-hero ) ]";

	/**
	 * XPath query to find the featured image.
	 *
	 * @var string
	 */
	const FEATURED_IMAGE_XPATH_QUERY = ".//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-post-image ' ) ][ not( @data-hero ) ]";

	/**
	 * XPath query to find the cover blocks.
	 *
	 * @var string
	 */
	const COVER_BLOCKS_XPATH_QUERY = ".//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-block-cover ' ) ][ not( @data-hero ) ]";

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document        $document DOM document to apply the
	 *                                  transformations to.
	 * @param ErrorCollection $errors   Collection of errors that are collected
	 *                                  during transformation.
	 * @return void
	 */
	public function transform( Document $document, ErrorCollection $errors ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$existing_hero_images_count = $document->xpath->query(
			self::EXISTING_HERO_IMAGES_XPATH_QUERY,
			$document->body
		)->length;

		$available_hero_image_slots = max(
			PreloadHeroImage::DATA_HERO_MAX - $existing_hero_images_count,
			0
		);

		$hero_image_elements = [];

		if ( count( $hero_image_elements ) < $available_hero_image_slots ) {
			$custom_logo = $this->get_custom_logo( $document );
			if ( null !== $custom_logo ) {
				$hero_image_elements[] = $custom_logo;
			}
		}

		if ( count( $hero_image_elements ) < $available_hero_image_slots ) {
			$featured_image = $this->get_featured_image( $document );
			if ( null !== $featured_image ) {
				$hero_image_elements[] = $featured_image;
			}
		}

		if ( count( $hero_image_elements ) < $available_hero_image_slots ) {
			$hero_image_elements = array_merge(
				$hero_image_elements,
				array_filter(
					$this->get_cover_blocks( $document )
				)
			);
		}

		$this->add_data_hero_attribute(
			array_slice( $hero_image_elements, 0, $available_hero_image_slots )
		);
	}

	/**
	 * Retrieve the element that represents the custom logo.
	 *
	 * @param Document $document Document to retrieve the custom logo from.
	 * @return DOMElement|null Element that represents the custom logo, or null
	 *                         if not found.
	 */
	private function get_custom_logo( Document $document ) {
		$elements = $document->xpath->query(
			self::CUSTOM_LOGO_XPATH_QUERY,
			$document->body
		);

		$custom_logo = $elements->item( 0 );

		return $custom_logo instanceof DOMElement ? $custom_logo : null;
	}

	/**
	 * Retrieve the element that represents the featured image.
	 *
	 * @param Document $document Document to retrieve the featured image from.
	 * @return DOMElement|null Element that represents the featured image, or
	 *                         null if not found.
	 */
	private function get_featured_image( Document $document ) {
		$elements = $document->xpath->query(
			self::FEATURED_IMAGE_XPATH_QUERY,
			$document->body
		);

		$featured_image = $elements->item( 0 );

		return $featured_image instanceof DOMElement ? $featured_image : null;
	}

	/**
	 * Retrieve the element(s) that are cover blocks.
	 *
	 * @param Document $document Document to retrieve the cover blocks from.
	 * @return DOMElement[] Array of elements that are cover blocks.
	 */
	private function get_cover_blocks( Document $document ) {
		$elements = $document->xpath->query(
			self::COVER_BLOCKS_XPATH_QUERY,
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
