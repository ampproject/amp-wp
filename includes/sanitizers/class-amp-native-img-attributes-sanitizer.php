<?php
/**
 * Native img attributes sanitizer.
 *
 * @since 2.3.1
 * @package AMP
 */

use AmpProject\Html\Attribute;
use AmpProject\Dom\Element;

/**
 * Class AMP_Native_Img_Attributes_Sanitizer
 *
 * @since 2.3.1
 * @internal
 */
class AMP_Native_Img_Attributes_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
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
		'native_img' => false,
	];

	/**
	 * Sanitize the Native img attributes.
	 *
	 * @since 2.3
	 */
	public function sanitize() {
		// Bail if native img is not being used.
		if ( ! isset( $this->args['native_img_used'] ) || ! $this->args['native_img_used'] ) {
			return;
		}

		$img_elements = $this->dom->xpath->query(
			'.//img[ @layout = "fill" or @object-fit = "cover" ]',
			$this->dom->body
		);

		if ( ! $img_elements instanceof DOMNodeList || 0 === $img_elements->length ) {
			return;
		}

		$style_layout_fill = 'position:absolute; left:0; right:0; top:0; bottom: 0; width:100%; height:100%;';
		$style_object_fit  = 'object-fit:cover;';

		foreach ( $img_elements as $img_element ) {
			/** @var Element $img_element */

			$remove_layout_attr     = $img_element->removeAttribute( Attribute::LAYOUT );
			$remove_object_fit_attr = $img_element->removeAttribute( Attribute::OBJECT_FIT );
			$style_attr_content     = sprintf( '%s %s', $remove_layout_attr ? $style_layout_fill : '', $remove_object_fit_attr ? $style_object_fit : '' );

			if ( ' ' !== $style_attr_content ) {
				$img_element->setAttribute( Attribute::STYLE, $style_attr_content );
			}
		}
	}
}
