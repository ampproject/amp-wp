<?php
/**
 * Class AMP_Dev_Mode_Sanitizer
 *
 * Add the data-ampdevmode to the document element and to the elements specified by the supplied args.
 *
 * @since 1.3
 * @package AMP
 */

use AmpProject\DevMode;

/**
 * Class AMP_Dev_Mode_Sanitizer
 *
 * @since 1.3
 */
final class AMP_Dev_Mode_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type string[] $element_xpaths XPath expressions for elements to add the data-ampdevmode attribute to.
	 * }
	 */
	protected $args;

	/**
	 * Sanitize document for dev mode.
	 *
	 * @since 1.3
	 */
	public function sanitize() {
		$document_element = $this->dom->documentElement;

		if ( ! DevMode::hasExemptionForNode( $document_element ) ) {
			$document_element->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '_amp_exempt' );
		}

		$element_xpaths = ! empty( $this->args['element_xpaths'] ) ? $this->args['element_xpaths'] : [];
		foreach ( $element_xpaths as $element_xpath ) {
			foreach ( $this->dom->xpath->query( $element_xpath ) as $node ) {
				if ( ! DevMode::hasExemptionForNode( $node ) ) {
					$node->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '_amp_exempt' );
				}
			}
		}
	}
}
