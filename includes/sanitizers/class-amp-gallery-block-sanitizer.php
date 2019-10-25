<?php
/**
 * Class AMP_Gallery_Block_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Gallery_Block_Sanitizer
 *
 * Modifies gallery block to match the block's AMP-specific configuration.
 */
class AMP_Gallery_Block_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Value used for width of amp-carousel.
	 *
	 * @since 1.0
	 *
	 * @const int
	 */
	const FALLBACK_WIDTH = 600;

	/**
	 * Value used for height of amp-carousel.
	 *
	 * @since 1.0
	 *
	 * @const int
	 */
	const FALLBACK_HEIGHT = 480;

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
		$xpath       = new DOMXPath( $this->dom );
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
		$query       = $xpath->query( $expr );

		$nodes = [];
		foreach ( $query as $node ) {
			$nodes[] = $node;
		}

		foreach ( $nodes as $node ) {
			/**
			 * Element
			 *
			 * @var DOMElement $node
			 */

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
				$this->maybe_add_amp_image_lightbox_node();
			}

			// If amp-carousel is not set, nothing else to do here.
			if ( ! $is_amp_carousel ) {
				continue;
			}

			$images = [];

			// If it's not AMP lightbox, look for links first.
			if ( ! $is_amp_lightbox ) {
				foreach ( $node->getElementsByTagName( 'a' ) as $element ) {
					$images[] = $element;
				}
			}

			// If not linking to anything then look for <amp-img>.
			if ( empty( $images ) ) {
				foreach ( $node->getElementsByTagName( 'amp-img' ) as $element ) {
					$images[] = $element;
				}
			}

			// Skip if no images found.
			if ( empty( $images ) ) {
				continue;
			}

			list( $width, $height ) = $this->get_carousel_dimensions( $node );

			$amp_carousel = AMP_DOM_Utils::create_node(
				$this->dom,
				'amp-carousel',
				[
					'width'  => $width,
					'height' => $height,
					'type'   => 'slides',
					'layout' => 'responsive',
				]
			);

			foreach ( $images as $image ) {
				$slide = AMP_DOM_Utils::create_node(
					$this->dom,
					'div',
					[ 'class' => 'slide' ]
				);

				// Ensure the image fills the entire <amp-carousel>, so the possible caption looks right.
				if ( 'amp-img' === $image->tagName ) {
					$image->setAttribute( 'layout', 'fill' );
					$image->setAttribute( 'object-fit', 'cover' );
				} elseif ( isset( $image->firstChild->tagName ) && 'amp-img' === $image->firstChild->tagName ) {
					// If the <amp-img> is wrapped in an <a>.
					$image->firstChild->setAttribute( 'layout', 'fill' );
					$image->firstChild->setAttribute( 'object-fit', 'cover' );
				}

				$possible_caption_text = $this->possibly_get_caption_text( $image );
				$slide->appendChild( $image );

				// Wrap the caption in a <div> and <span>, and append it to the slide.
				if ( $possible_caption_text ) {
					$caption_wrapper = AMP_DOM_Utils::create_node(
						$this->dom,
						'div',
						[ 'class' => 'amp-wp-gallery-caption' ]
					);
					$caption_span    = AMP_DOM_Utils::create_node( $this->dom, 'span', [] );
					$text_node       = $this->dom->createTextNode( $possible_caption_text );

					$caption_span->appendChild( $text_node );
					$caption_wrapper->appendChild( $caption_span );
					$slide->appendChild( $caption_wrapper );
				}

				$amp_carousel->appendChild( $slide );
			}

			$gallery_node->parentNode->replaceChild( $amp_carousel, $gallery_node );
		}
		$this->did_convert_elements = true;
	}

	/**
	 * Get carousel height by containing images.
	 *
	 * @param DOMElement $element The UL element.
	 * @return array {
	 *     Dimensions.
	 *
	 *     @type int $width  Width.
	 *     @type int $height Height.
	 * }
	 */
	protected function get_carousel_dimensions( $element ) {
		/**
		 * Elements.
		 *
		 * @var DOMElement $image
		 */
		$images     = $element->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;

		$max_aspect_ratio = 0;
		$carousel_width   = 0;
		$carousel_height  = 0;

		if ( 0 === $num_images ) {
			return [ self::FALLBACK_WIDTH, self::FALLBACK_HEIGHT ];
		}
		foreach ( $images as $image ) {
			if ( ! is_numeric( $image->getAttribute( 'width' ) ) || ! is_numeric( $image->getAttribute( 'height' ) ) ) {
				continue;
			}
			$width  = (float) $image->getAttribute( 'width' );
			$height = (float) $image->getAttribute( 'height' );

			$this_aspect_ratio = $width / $height;
			if ( $this_aspect_ratio > $max_aspect_ratio ) {
				$max_aspect_ratio = $this_aspect_ratio;
				$carousel_width   = $width;
				$carousel_height  = $height;
			}
		}

		return [ $carousel_width, $carousel_height ];
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
		$attributes = [
			'data-amp-lightbox' => '',
			'on'                => 'tap:' . self::AMP_IMAGE_LIGHTBOX_ID,
			'role'              => 'button',
			'tabindex'          => 0,
		];

		for ( $j = $num_images - 1; $j >= 0; $j-- ) {
			$image_node = $images->item( $j );
			foreach ( $attributes as $att => $value ) {
				$image_node->setAttribute( $att, $value );
			}
		}
	}

	/**
	 * Gets the caption of an image, if it exists.
	 *
	 * @param DOMElement $element The element for which to search for a caption.
	 * @return string|null The caption for the image, or null.
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

		return null;
	}
}
