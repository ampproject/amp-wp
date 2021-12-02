<?php
/**
 * Trait HandlesGalleryEmbed.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Embed;

use AmpProject\AmpWP\Component\Carousel;
use AmpProject\AmpWP\Dom\ElementList;
use AmpProject\Dom\Document;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use DOMElement;
use DOMNodeList;

/**
 * Trait HandlesGalleryEmbed.
 *
 * Contains logic related to both gallery shortcodes and blocks.
 *
 * @since 2.0
 * @internal
 */
trait HandlesGalleryEmbed {

	/**
	 * Transforms the raw gallery embed to become AMP compatible.
	 *
	 * @param bool        $is_carousel     Whether the embed should be transformed into an <amp-carousel>.
	 * @param bool        $is_lightbox     Whether the gallery images should be shown in a lightbox.
	 * @param DOMElement  $gallery_element Gallery element.
	 * @param DOMNodeList $img_elements    List of image elements in gallery.
	 */
	protected function process_gallery_embed( $is_carousel, $is_lightbox, DOMElement $gallery_element, DOMNodeList $img_elements ) {
		// Bail if the embed does not support carousel or lightbox.
		if ( ! $is_carousel && ! $is_lightbox ) {
			return;
		}

		// Bail if there are no images.
		if ( 0 === $img_elements->length ) {
			return;
		}

		// If the carousel is not required but the lightbox is, add the `lightbox` attribute to each image and return.
		if ( ! $is_carousel && $is_lightbox ) {
			$this->add_lightbox_attribute_to_img_nodes( $img_elements );
			return;
		}

		if ( $is_carousel ) {
			$amp_carousel     = $this->generate_amp_carousel( $img_elements, $is_lightbox );
			$carousel_element = $amp_carousel->get_dom_element();

			if ( $is_lightbox ) {
				$carousel_element->setAttribute( Attribute::LIGHTBOX, '' );
			}

			$gallery_element->parentNode->replaceChild( $carousel_element, $gallery_element );
		}
	}

	/**
	 * Create an AMP carousel component from the list of images specified.
	 *
	 * @param DOMNodeList $img_elements    List of images in the gallery.
	 * @param boolean     $is_amp_lightbox Whether the gallery should have a lightbox.
	 * @return Carousel An object containing markup for <amp-carousel>.
	 */
	protected function generate_amp_carousel( DOMNodeList $img_elements, $is_amp_lightbox ) {
		$images = new ElementList();

		foreach ( $img_elements as $img_element ) {
			$element             = $img_element;
			$parent_element_name = $img_element->parentNode->nodeName;

			if ( Tag::A === $parent_element_name && ! $is_amp_lightbox ) {
				$element = $img_element->parentNode;
			}

			$images = $images->add( $element, $this->get_caption_element( $img_element ) );
		}

		$node = $img_elements->item( 0 );
		return new Carousel( Document::fromNode( $node ), $images );
	}

	/**
	 * Sets the `lightbox` attribute to each image in the specified list.
	 *
	 * @param DOMNodeList $img_elements List of image elements.
	 */
	protected function add_lightbox_attribute_to_img_nodes( DOMNodeList $img_elements ) {
		/** @var DOMElement $img_element */
		foreach ( $img_elements as $img_element ) {
			$img_element->setAttribute( Attribute::LIGHTBOX, '' );
		}
	}
}
