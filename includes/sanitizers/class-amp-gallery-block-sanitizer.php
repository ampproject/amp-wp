<?php
/**
 * Class AMP_Gallery_Block_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Dom\ElementList;
use AmpProject\AmpWP\Component\Carousel;

/**
 * Class AMP_Gallery_Block_Sanitizer
 *
 * Modifies gallery block to match the block's AMP-specific configuration.
 */
class AMP_Gallery_Block_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @since 1.0
	 *
	 * @var string Ul tag to identify wrapper around gallery block.
	 */
	public static $tag = 'ul';

	/**
	 * Expected class of the wrapper around the gallery block.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public static $class = 'wp-block-gallery';

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type int  $content_max_width Max width of content.
	 *      @type bool $carousel_required Whether carousels are required. This is used when amp theme support is not present, for back-compat.
	 * }
	 */
	protected $args;

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'carousel_required' => false,
	];

	/**
	 * Sanitize the gallery block contained by <ul> element where necessary.
	 *
	 * @since 0.2
	 */
	public function sanitize() {
		$class_query = 'contains( concat( " ", normalize-space( @class ), " " ), " wp-block-gallery " )';
		$expr        = sprintf(
			'//ul[ %s ]',
			implode(
				' or ',
				[
					sprintf( '( parent::figure[ %s ] )', $class_query ),
					$class_query,
				]
			)
		);
		$query       = $this->dom->xpath->query( $expr );

		$nodes = [];
		foreach ( $query as $node ) {
			$nodes[] = $node;
		}

		foreach ( $nodes as $node ) {
			// In WordPress 5.3, the Gallery block's <ul> is wrapped in a <figure class="wp-block-gallery">, so look for that node also.
			$gallery_node = isset( $node->parentNode ) && AMP_DOM_Utils::has_class( $node->parentNode, self::$class ) ? $node->parentNode : $node;
			$attributes   = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $gallery_node );

			$is_amp_lightbox = isset( $attributes['data-amp-lightbox'] ) && true === filter_var( $attributes['data-amp-lightbox'], FILTER_VALIDATE_BOOLEAN );
			$is_amp_carousel = (
				! empty( $this->args['carousel_required'] )
				||
				filter_var( $node->getAttribute( 'data-amp-carousel' ), FILTER_VALIDATE_BOOLEAN )
				||
				filter_var( $node->parentNode->getAttribute( 'data-amp-carousel' ), FILTER_VALIDATE_BOOLEAN )
			);

			// We are only looking for <ul> elements which have amp-carousel / amp-lightbox true.
			if ( ! $is_amp_carousel && ! $is_amp_lightbox ) {
				continue;
			}

			// If lightbox is set, we should add lightbox feature to the gallery images.
			if ( $is_amp_lightbox ) {
				$this->add_lightbox_attributes_to_image_nodes( $node );
			}

			// If amp-carousel is not set, nothing else to do here.
			if ( ! $is_amp_carousel ) {
				continue;
			}

			$images = new ElementList();

			// If it's not AMP lightbox, look for links first.
			if ( ! $is_amp_lightbox ) {
				foreach ( $node->getElementsByTagName( 'a' ) as $element ) {
					$images = $images->add( $element, $this->possibly_get_caption_text( $element ) );
				}
			}

			// If not linking to anything then look for <amp-img>.
			if ( 0 === count( $images ) ) {
				foreach ( $node->getElementsByTagName( 'amp-img' ) as $element ) {
					$images = $images->add( $element, $this->possibly_get_caption_text( $element ) );
				}
			}

			// Skip if no images found.
			if ( 0 === count( $images ) ) {
				continue;
			}

			$amp_carousel = new Carousel( $this->dom, $images );
			$gallery_node->parentNode->replaceChild( $amp_carousel->get_dom_element(), $gallery_node );
		}
		$this->did_convert_elements = true;
	}

	/**
	 * Set lightbox related attributes to <amp-img> within gallery.
	 *
	 * @param DOMElement $element The UL element.
	 */
	protected function add_lightbox_attributes_to_image_nodes( $element ) {
		$images     = $element->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;
		if ( 0 === $num_images ) {
			return;
		}

		for ( $j = $num_images - 1; $j >= 0; $j-- ) {
			$image_node = $images->item( $j );
			$image_node->setAttribute( 'lightbox', '' );
		}
	}

	/**
	 * Gets the caption of an image, if it exists.
	 *
	 * @param DOMElement $element The element for which to search for a caption.
	 * @return string The caption for the image, or ''.
	 */
	public function possibly_get_caption_text( $element ) {
		$caption_tag = 'figcaption';
		if ( isset( $element->nextSibling->nodeName ) && $caption_tag === $element->nextSibling->nodeName ) {
			return $element->nextSibling->textContent;
		}

		// If 'Link To' is selected, the image will be wrapped in an <a>, so search for the sibling of the <a>.
		if ( isset( $element->parentNode->nextSibling->nodeName ) && $caption_tag === $element->parentNode->nextSibling->nodeName ) {
			return $element->parentNode->nextSibling->textContent;
		}

		return '';
	}
}
