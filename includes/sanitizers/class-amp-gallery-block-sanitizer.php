<?php
/**
 * Class AMP_Gallery_Block_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Embed\HandlesGalleryEmbed;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Dom\Element;

/**
 * Class AMP_Gallery_Block_Sanitizer
 *
 * Modifies gallery block to match the block's AMP-specific configuration.
 *
 * @internal
 */
class AMP_Gallery_Block_Sanitizer extends AMP_Base_Sanitizer {

	use HandlesGalleryEmbed;

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
	 *      @type bool $native_img_used   Whether native img is being used.
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
		'native_img'        => false,
	];

	/**
	 * Sanitize the gallery block contained by elements with the wp-block-gallery class.
	 *
	 * The markup structure has changed over time:
	 *
	 *  - WordPress<5.2: ul.wp-block-gallery > li
	 *  - WordPress<5.9: figure.wp-block-gallery > ul > li > figure > img
	 *  - WordPress≥5.9: figure.wp-block-gallery > figure.wp-block-image > img
	 *
	 * @since 0.2
	 */
	public function sanitize() {
		$class_query = 'contains( concat( " ", normalize-space( @class ), " " ), " wp-block-gallery " )';

		$gallery_elements = $this->dom->xpath->query(
			sprintf( './/ul[ %1$s ] | .//figure[ %1$s ]', $class_query ),
			$this->dom->body
		);
		foreach ( $gallery_elements as $gallery_element ) {
			/** @var Element $gallery_element */

			$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $gallery_element );

			$is_amp_lightbox = isset( $attributes['data-amp-lightbox'] ) && rest_sanitize_boolean( $attributes['data-amp-lightbox'] );

			if ( isset( $attributes['data-amp-carousel'] ) ) {
				$is_amp_carousel = rest_sanitize_boolean( $attributes['data-amp-carousel'] );
			} else {
				// The carousel_required argument is set to true when the theme does not support AMP. However, it is no
				// no longer strictly required. Rather, carousels are just enabled by default.
				$is_amp_carousel = ! empty( $this->args['carousel_required'] );
			}

			// Ensure data-amp-carousel=true attribute is present for proper styling of block.
			if ( $is_amp_carousel ) {
				$gallery_element->setAttribute( 'data-amp-carousel', 'true' );
			}

			$img_elements = $this->dom->xpath->query(
				empty( $this->args['native_img_used'] ) ? './/amp-img | .//amp-anim' : './/img',
				$gallery_element
			);

			$this->process_gallery_embed( $is_amp_carousel, $is_amp_lightbox, $gallery_element, $img_elements );
		}

		if ( ! isset( $this->args['native_img_used'] ) || ! $this->args['native_img_used'] ) {
			return;
		}

		/*
		* Remove `layout` and `object-fit` attributes from native img as they are not supported by AMP yet.
		* Add style attribute which is used to set `object-fit` and `layout` attributes.
		* @see <https://github.com/ampproject/amp-wp/issues/7152#issuecomment-1157933188>
		* @todo Remove this once `layout` and `object-fit` attributes are supported by AMP spec for native img tag.
		*/
		$style_layout_fill = 'position:absolute; left:0; right:0; top:0; bottom: 0; width:100%; height:100%;';
		$style_object_fit  = 'object-fit:cover;';
		foreach ( $img_elements as $img_element ) {
			$remove_layout_attr     = $img_element->removeAttribute( Attribute::LAYOUT );
			$remove_object_fit_attr = $img_element->removeAttribute( Attribute::OBJECT_FIT );
			$style_attr_content     = sprintf( '%s %s', $remove_layout_attr ? $style_layout_fill : '', $remove_object_fit_attr ? $style_object_fit : '' );

			if ( ! empty( $style_attr_content ) ) {
				$img_element->setAttribute( Attribute::STYLE, $style_attr_content );
			}
		}
	}

	/**
	 * Get the caption element for the specified image element.
	 *
	 * @param DOMElement $img_element Image element.
	 * @return DOMElement|null The caption element, or `null` if the image has none.
	 */
	protected function get_caption_element( DOMElement $img_element ) {
		$figcaption_element = null;
		if (
			isset( $img_element->nextSibling->nodeName )
			&& $img_element->nextSibling instanceof DOMElement
			&& Tag::FIGCAPTION === $img_element->nextSibling->nodeName
		) {
			$figcaption_element = $img_element->nextSibling;
		}

		// If 'Link To' is selected, the image will be wrapped in an <a>, so search for the sibling of the <a>.
		if (
			! $figcaption_element
			&& isset( $img_element->parentNode->nextSibling->nodeName )
			&& $img_element->parentNode->nextSibling instanceof DOMElement
			&& Tag::FIGCAPTION === $img_element->parentNode->nextSibling->nodeName
		) {
			$figcaption_element = $img_element->parentNode->nextSibling;
		}

		if ( $figcaption_element instanceof DOMElement && 0 === $figcaption_element->childNodes->length ) {
			return null;
		}

		return $figcaption_element;
	}
}
