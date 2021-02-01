<?php
/**
 * Class AMP_Accessibility_Sanitizer.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\Attribute;
use AmpProject\Role;

/**
 * Sanitizes attributes required for AMP accessibility requirements.
 *
 * @since 1.5.3
 * @internal
 */
class AMP_Accessibility_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$this->add_role_and_tabindex_to_on_tap_actors();
	}

	/**
	 * Adds the role and tabindex attributes to all elements that use a tap action via AMP's "on" event.
	 */
	public function add_role_and_tabindex_to_on_tap_actors() {
		$predicates = [
			'@on',
			'contains( @on, "tap:" )',
			'not( @tabindex ) or not( @role )',
			'not( self::button )',
			'not( self::a[ @href ] )',
		];

		$expression = sprintf(
			'//*[ %s ]',
			implode(
				' and ',
				array_map(
					static function ( $predicate ) {
						return "( $predicate )";
					},
					$predicates
				)
			)
		);

		$attributes = [
			Attribute::ROLE     => Role::BUTTON,
			Attribute::TABINDEX => '0',
		];

		/**
		 * Element.
		 *
		 * @var DOMElement $element
		 */
		foreach ( $this->dom->xpath->query( $expression ) as $element ) {
			foreach ( $attributes as $attribute_name => $attribute_value ) {
				if ( ! $element->hasAttribute( $attribute_name ) ) {
					$element->setAttribute( $attribute_name, $attribute_value );
				}
			}
		}
	}
}
