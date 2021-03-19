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
	 * XPath query to find the custom logo.
	 *
	 * @var string
	 */
	const CUSTOM_HEADER_XPATH_QUERY = ".//*[ @id = 'wp-custom-header' or @id = 'masthead' or @id = 'site-header' or contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-custom-header ' ) ]//*[ ( self::img or self::amp-img ) and not( @data-hero ) and not( contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo ' ) ) ]";

	/**
	 * XPath query to find the custom logo.
	 *
	 * @var string
	 */
	const CUSTOM_LOGO_XPATH_QUERY = ".//a[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo-link ' ) ]//*[ ( self::img or self::amp-img ) and contains( concat( ' ', normalize-space( @class ), ' ' ), ' custom-logo ' ) ][ not( @data-hero ) ]";

	/**
	 * XPath query to find the featured image.
	 *
	 * @var string
	 */
	const FEATURED_IMAGE_XPATH_QUERY = ".//*[ ( self::img or self::amp-img ) and contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-post-image ' ) ][ not( @data-hero ) ]";

	/**
	 * XPath query to find the first entry-content.
	 *
	 * @var string
	 */
	const FIRST_ENTRY_CONTENT = ".//*[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' entry-content ' ) ][1]";

	/**
	 * XPath query to find background image of a Cover Block at the beginning of post content (including nested inside of another block).
	 *
	 * @var string
	 */
	const INITIAL_COVER_BLOCK_XPATH_QUERY = "./*[1]/descendant-or-self::div[ contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-block-cover ' ) ]/*[ ( self::img or self::amp-img ) and contains( concat( ' ', normalize-space( @class ), ' ' ), ' wp-block-cover__image-background ' ) ][ not( @data-hero ) ]";

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
		$hero_image_elements = [];

		foreach ( [ 'custom_header', 'custom_logo', 'featured_image', 'cover_blocks' ] as $hero_image_source ) {
			if ( count( $hero_image_elements ) < PreloadHeroImage::DATA_HERO_MAX ) {
				$candidates = [];

				switch ( $hero_image_source ) {
					case 'custom_header':
						$candidates = $this->get_custom_header( $document );
						break;
					case 'custom_logo':
						$candidates = $this->get_custom_logo( $document );
						break;
					case 'featured_image':
						$candidates = $this->get_featured_image( $document );
						break;
					case 'cover_blocks':
						$candidates = $this->get_initial_content_cover_block( $document );
						break;
				}

				if ( empty( $candidates ) ) {
					continue;
				}

				if ( is_array( $candidates ) ) {
					$hero_image_elements = array_merge( $hero_image_elements, array_filter( $candidates ) );
				} else {
					$hero_image_elements[] = $candidates;
				}
			}
		}

		$this->add_data_hero_candidate_attribute(
			array_slice( $hero_image_elements, 0, PreloadHeroImage::DATA_HERO_MAX )
		);
	}

	/**
	 * Retrieve the element that represents the custom header.
	 *
	 * @param Document $document Document to retrieve the custom header from.
	 * @return DOMElement|null Element that represents the custom header, or null
	 *                         if not found.
	 */
	private function get_custom_header( Document $document ) {
		$elements = $document->xpath->query(
			self::CUSTOM_HEADER_XPATH_QUERY,
			$document->body
		);

		$custom_header = $elements->item( 0 );

		return $custom_header instanceof DOMElement ? $custom_header : null;
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
	 * Retrieve the first cover block that is in the first position in content.
	 *
	 * @param Document $document Document to retrieve the cover blocks from.
	 * @return DOMElement|null Cover block at the beginning of the first entry content.
	 */
	private function get_initial_content_cover_block( Document $document ) {
		$query = $document->xpath->query(
			self::FIRST_ENTRY_CONTENT,
			$document->body
		);

		$entry_content = $query->item( 0 );
		if ( ! $entry_content instanceof DOMElement ) {
			return null;
		}

		$query = $document->xpath->query(
			self::INITIAL_COVER_BLOCK_XPATH_QUERY,
			$entry_content
		);

		$cover_block_image = $query->item( 0 );
		return $cover_block_image instanceof DOMElement ? $cover_block_image : null;
	}

	/**
	 * Add the data-hero attribute to viable hero images.
	 *
	 * @param DOMElement[] $hero_image_elements Elements that are viable hero
	 *                                          images.
	 */
	private function add_data_hero_candidate_attribute( $hero_image_elements ) {
		foreach ( $hero_image_elements as $hero_image_element ) {
			$hero_image_element->setAttribute( Attribute::DATA_HERO_CANDIDATE, null );
		}
	}
}
