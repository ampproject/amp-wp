<?php
/**
 * Class AMP_Nav_Menu_Toggle_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Nav_Menu_Toggle_Sanitizer
 *
 * Handles state for navigation menu toggles, based on theme support.
 *
 * @since 1.1.0
 */
class AMP_Nav_Menu_Toggle_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * XPath.
	 *
	 * @since 1.1.0
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 * Get default args.
	 *
	 * @since 1.3
	 * @return array Default args.
	 */
	public static function get_default_args() {
		$args = [
			'nav_container_id'           => '',
			'nav_container_xpath'        => '', // Alternative for 'nav_container_id', if no ID available.
			'menu_button_id'             => '',
			'menu_button_xpath'          => '', // Alternative for 'menu_button_id', if no ID available.
			'nav_container_toggle_class' => '',
			'menu_button_toggle_class'   => '', // Optional.
			'nav_menu_toggle_state_id'   => 'navMenuToggledOn',
		];

		$theme_support_args = AMP_Theme_Support::get_theme_support_args();
		if ( ! empty( $theme_support_args['nav_menu_toggle'] ) ) {
			$args = array_merge( $args, $theme_support_args['nav_menu_toggle'] );
		}

		return $args;
	}

	/**
	 * If supported per the constructor arguments, inject `amp-state` and bind dynamic classes accordingly.
	 *
	 * @since 1.1.0
	 */
	public function sanitize() {
		$this->xpath = new DOMXPath( $this->dom );

		$nav_el    = $this->get_nav_container();
		$button_el = $this->get_menu_button();

		// If no navigation element or no toggle class provided, bail.
		if ( ! $nav_el ) {
			if ( $button_el ) {

				// Remove the button since it won't be used.
				$button_el->parentNode->removeChild( $button_el );
			}
			return;
		}

		if ( ! $button_el ) {
			return;
		}

		$state_id = 'navMenuToggledOn';
		$expanded = false;

		if ( ! empty( $this->args['nav_container_toggle_class'] ) ) {
			$nav_el->setAttribute(
				AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'class',
				sprintf(
					"%s + ( $state_id ? %s : '' )",
					wp_json_encode( $nav_el->getAttribute( 'class' ) ),
					wp_json_encode( ' ' . $this->args['nav_container_toggle_class'] )
				)
			);
		}

		$state_el = $this->dom->createElement( 'amp-state' );
		$state_el->setAttribute( 'id', $state_id );
		$script_el = $this->dom->createElement( 'script' );
		$script_el->setAttribute( 'type', 'application/json' );
		$script_el->appendChild( $this->dom->createTextNode( wp_json_encode( $expanded ) ) );
		$state_el->appendChild( $script_el );
		if ( 'body' === $nav_el->nodeName ) {
			$nav_el->insertBefore( $state_el, $nav_el->firstChild );
		} elseif ( $nav_el === $this->dom->documentElement ) {
			$body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
			$body->insertBefore( $state_el, $body->firstChild );
		} else {
			$nav_el->parentNode->insertBefore( $state_el, $nav_el );
		}

		$button_on = sprintf( "tap:AMP.setState({ $state_id: ! $state_id })" );
		$button_el->setAttribute( 'on', $button_on );
		$button_el->setAttribute( 'aria-expanded', 'false' );
		$button_el->setAttribute( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'aria-expanded', "$state_id ? 'true' : 'false'" );
		if ( ! empty( $this->args['menu_button_toggle_class'] ) ) {
			$button_el->setAttribute(
				AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'class',
				sprintf( "%s + ( $state_id ? %s : '' )", wp_json_encode( $button_el->getAttribute( 'class' ) ), wp_json_encode( ' ' . $this->args['menu_button_toggle_class'] ) )
			);
		}
	}

	/**
	 * Retrieves the navigation container element.
	 *
	 * @since 1.1.0
	 *
	 * @return DOMElement|null Navigation container element, or null if not provided or found.
	 */
	protected function get_nav_container() {
		if ( ! empty( $this->args['nav_container_id'] ) ) {
			return $this->dom->getElementById( $this->args['nav_container_id'] );
		}

		if ( ! empty( $this->args['nav_container_xpath'] ) ) {
			return $this->xpath->query( $this->args['nav_container_xpath'] )->item( 0 );
		}

		return null;
	}

	/**
	 * Retrieves the navigation menu button element.
	 *
	 * @since 1.1.0
	 *
	 * @return DOMElement|null Navigation menu button element, or null if not provided or found.
	 */
	protected function get_menu_button() {
		if ( ! empty( $this->args['menu_button_id'] ) ) {
			return $this->dom->getElementById( $this->args['menu_button_id'] );
		}

		if ( ! empty( $this->args['menu_button_xpath'] ) ) {
			return $this->xpath->query( $this->args['menu_button_xpath'] )->item( 0 );
		}

		return null;
	}
}
