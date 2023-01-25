<?php
/**
 * Class DetermineHeroImages.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Optimizer\Transformer;

use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\ImageDimensions;
use AmpProject\Optimizer\Transformer;

/**
 * Determine the images to flag with data-hero-candidate so the Optimizer can prerender them.
 *
 * This transformer checks for the following images in the given order:
 * 1. Header images (including Custom Logo and Custom Header)
 * 2. Image which is descendant of first child of first entry content
 *
 * @package AmpProject\AmpWP
 * @since   2.1
 * @internal
 */
final class DetermineHeroImages implements Transformer {

	/**
	 * XPath query to find preceding images which are not lazy-loaded.
	 *
	 * @var string
	 */
	const PRECEDING_NON_LAZY_IMAGE_XPATH_QUERY = "preceding::amp-img[ not( @data-hero ) ][ not( noscript/img/@loading ) or noscript/img/@loading != 'lazy' ]";

	/**
	 * XPath query to find the first entry-content.
	 *
	 * Note that the 'entry-content' class name is the classic form for what the h-entry spec now has as 'e-content'.
	 * The 'amp-wp-article-content' class name is used in legacy Reader templates. Note that 'entry-content' isn't
	 * simply just added to templates/single.php and templates/page.php because these templates are frequently forked.
	 *
	 * @link https://microformats.org/wiki/h-entry
	 * @var string
	 */
	const FIRST_ENTRY_CONTENT_XPATH_QUERY = "
		.//*[ @class ][
			contains( concat( ' ', normalize-space( @class ), ' ' ), ' entry-content ' )
			or
			contains( concat( ' ', normalize-space( @class ), ' ' ), ' e-content ' )
			or
			contains( concat( ' ', normalize-space( @class ), ' ' ), ' amp-wp-article-content ' )
		]
	";

	/**
	 * XPath query to find an image at the beginning of entry content (including nested inside of another block).
	 *
	 * @var string
	 */
	const INITIAL_CONTENT_IMAGE_XPATH_QUERY = './*[1]//amp-img[ not( @data-hero ) ]';

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

		foreach ( [ 'header_images', 'initial_content_image' ] as $hero_image_source ) {
			$candidate = null;

			switch ( $hero_image_source ) {
				case 'header_images':
					$candidate = $this->get_header_images( $document );
					break;
				case 'initial_content_image':
					$candidate = $this->get_initial_content_image( $document );
					break;
			}

			if ( $candidate instanceof Element ) {
				$hero_image_elements[ spl_object_hash( $candidate ) ] = $candidate;
			} elseif ( is_array( $candidate ) ) {
				foreach ( $candidate as $hero_image_element ) {
					$hero_image_elements[ spl_object_hash( $hero_image_element ) ] = $hero_image_element;
				}
			}
		}

		$this->add_data_hero_candidate_attribute(
			array_values( $hero_image_elements )
		);
	}

	/**
	 * Retrieve the images in the header.
	 *
	 * This returns all non-tiny images which occur before the main content, or else a tiny image that has `logo` in the
	 * class name.
	 *
	 * Note that the `HeroCandidateFiltering` service will have already identified the Header Image and Custom Logo for
	 * any theme using the standard `the_header_image_tag()` and `the_custom_logo()` template tags, respectively. This
	 * remains for here as a fallback to identify Header Images and Custom Logos being rendered via non-standard
	 * template tags.
	 *
	 * @see \AmpProject\AmpWP\Optimizer\HeroCandidateFiltering::add_custom_logo_data_hero_candidate_attribute()
	 * @see \AmpProject\AmpWP\Optimizer\HeroCandidateFiltering::filter_header_image_tag()
	 *
	 * @param Document $document Document to retrieve the header images from.
	 * @return Element[] Header images.
	 */
	private function get_header_images( Document $document ) {
		// Note that 3,508 out of 3,923 themes on WP.org  (89%) use the <main> element.
		$after_header_element = $document->getElementsByTagName( 'main' )->item( 0 );

		// If a theme happens to not use the <main> element, then fall back to using the first entry-content.
		if ( ! $after_header_element instanceof Element ) {
			$after_header_element = $this->get_first_entry_content( $document );
		}

		if ( ! $after_header_element instanceof Element ) {
			return [];
		}

		$query = $document->xpath->query(
			self::PRECEDING_NON_LAZY_IMAGE_XPATH_QUERY,
			$after_header_element
		);

		return array_filter(
			iterator_to_array( $query ),
			static function ( Element $element ) {
				// A custom logo may in fact be tiny and yet since it is in the header it should be prerendered.
				// Note that a theme may not be using `the_custom_logo()` template tag and that is why the `custom-logo`
				// class is not being checked for specifically.
				if (
					$element->hasAttribute( Attribute::CLASS_ )
					&&
					false !== strpos( $element->getAttribute( Attribute::CLASS_ ), 'logo' )
				) {
					return true;
				}

				return ! ( new ImageDimensions( $element ) )->isTiny();
			}
		);
	}

	/**
	 * Retrieve the first entry content.
	 *
	 * @param Document $document Document to retrieve the first entry content from.
	 * @return Element|null First entry content element.
	 */
	private function get_first_entry_content( Document $document ) {
		$query = $document->xpath->query(
			self::FIRST_ENTRY_CONTENT_XPATH_QUERY,
			$document->body
		);

		$entry_content = $query->item( 0 );
		return $entry_content instanceof Element ? $entry_content : null;
	}

	/**
	 * Retrieve the first image that is in the first position in content.
	 *
	 * @param Document $document Document to retrieve the image from.
	 * @return Element|null Image at the beginning of the first entry content.
	 */
	private function get_initial_content_image( Document $document ) {
		$entry_content = $this->get_first_entry_content( $document );
		if ( ! $entry_content instanceof Element ) {
			return null;
		}

		$query = $document->xpath->query(
			self::INITIAL_CONTENT_IMAGE_XPATH_QUERY,
			$entry_content
		);

		$image = $query->item( 0 );
		if ( $image instanceof Element && ! ( new ImageDimensions( $image ) )->isTiny() ) {
			return $image;
		}

		return null;
	}

	/**
	 * Add the data-hero attribute to viable hero images.
	 *
	 * @param Element[] $hero_image_elements Elements that are viable hero images.
	 */
	private function add_data_hero_candidate_attribute( $hero_image_elements ) {
		foreach ( $hero_image_elements as $hero_image_element ) {
			$hero_image_element->setAttribute( Attribute::DATA_HERO_CANDIDATE, null );
		}
	}
}
