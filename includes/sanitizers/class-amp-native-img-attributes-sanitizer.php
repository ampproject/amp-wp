<?php
/**
 * Native img attributes sanitizer.
 *
 * @since 2.4.0
 * @package AMP
 */

use AmpProject\Html\Attribute;
use AmpProject\Dom\Element;
use AmpProject\Layout;

/**
 * Class AMP_Native_Img_Attributes_Sanitizer
 *
 * @since 2.4.0
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

		// Images with layout=fill.
		$img_elements = $this->dom->xpath->query(
			'.//img[ @layout = "fill" ]',
			$this->dom->body
		);
		if ( $img_elements instanceof DOMNodeList ) {
			foreach ( $img_elements as $img_element ) {
				/** @var Element $img_element */
				$img_element->removeAttribute( Attribute::LAYOUT );
				$img_element->addInlineStyle( 'position:absolute;left:0;right:0;top:0;bottom:0;width:100%;height:100%' );
			}
		}

		// Images with object-fit attributes.
		$img_elements = $this->dom->xpath->query(
			'.//img[ @object-fit ]',
			$this->dom->body
		);
		if ( $img_elements instanceof DOMNodeList ) {
			foreach ( $img_elements as $img_element ) {
				/** @var Element $img_element */
				$value = $img_element->getAttribute( Attribute::OBJECT_FIT );
				$img_element->removeAttribute( Attribute::OBJECT_FIT );
				$img_element->addInlineStyle( sprintf( 'object-fit:%s', $value ) );
			}
		}

		// Images with object-position attributes.
		$img_elements = $this->dom->xpath->query(
			'.//img[ @object-position ]',
			$this->dom->body
		);
		if ( $img_elements instanceof DOMNodeList ) {
			foreach ( $img_elements as $img_element ) {
				/** @var Element $img_element */
				$value = $img_element->getAttribute( Attribute::OBJECT_POSITION );
				$img_element->removeAttribute( Attribute::OBJECT_POSITION );
				$img_element->addInlineStyle( sprintf( 'object-position:%s', $value ) );
			}
		}
	}
}
