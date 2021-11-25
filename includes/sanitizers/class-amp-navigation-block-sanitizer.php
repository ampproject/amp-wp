<?php
/**
 * Class AMP_Navigation_Block_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Layout;

/**
 * Class AMP_Navigation_Block_Sanitizer
 *
 * Modifies navigation block to match the block's AMP-specific configuration.
 *
 * @internal
 */
class AMP_Navigation_Block_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @since 2.2
	 *
	 * @var string Nav tag to identify wrapper around navigation block.
	 */
	public static $tag = 'nav';

	/**
	 * Expected class of the wrapper around the navigation block.
	 *
	 * @since 2.2
	 *
	 * @var string
	 */
	public static $class = 'wp-block-navigation';

	/**
	 * Sanitize the navigation block contained by <nav> element.
	 *
	 * Steps:
	 * - "fake" the "open" state by adding `is-menu-open has-modal-open` classes to `div.wp-block-navigation__responsive-container`
	 * - add `on="tap:navigationX.open"` to `button.wp-block-navigation__responsive-container-open` element
	 * - add `on="tap:navigationX.close"` to `button.wp-block-navigation__responsive-container-close` element
	 * - wrap `div.wp-block-navigation__responsive-container` with `<amp-lightbox id="navigationX" layout="nodisplay">...</amp-lightbox>`
	 * - remove `data-micromodal-trigger` and `data-micromodal-close` attributes
	 * - remove `aria-expanded` and `aria-modal` attributes
	 * - duplicate `div.wp-block-navigation__responsive-container` (original one, without extra classes) outside the `amp-lightbox` wrapper and unwrap it from modal-related wrappers
	 * - dequeue the `wp-block-navigation-view` script (see AMP_Navigation_Block_Sanitizer::add_buffering_hooks method)
	 *
	 * @see https://github.com/ampproject/amp-wp/issues/6319#issuecomment-978246093
	 * @since 2.2
	 */
	public function sanitize() {
		$nodes = $this->dom->xpath->query( $this->generate_class_query( self::$class, self::$tag ) );

		foreach ( $nodes as $index => $node ) {
			/** @var DOMElement $node */

			$container_node = $this->dom->xpath->query( $this->generate_class_query( 'wp-block-navigation__responsive-container' ), $node )->item( 0 );
			if ( ! $container_node instanceof DOMElement ) {
				continue;
			}

			$container_node->removeAttribute( Attribute::ID );

			$cloned_container_node = $container_node->cloneNode( true );
			$cloned_container_node->setAttribute( Attribute::CLASS_, trim( $cloned_container_node->getAttribute( Attribute::CLASS_ ) ) . ' is-menu-open has-modal-open' );

			$element_id        = sprintf( 'navigation%d', $index + 1 );
			$amp_lightbox_node = AMP_DOM_Utils::create_node(
				$this->dom,
				Extension::LIGHTBOX,
				[
					Attribute::ID     => $element_id,
					Attribute::LAYOUT => Layout::NODISPLAY,
				]
			);

			$amp_lightbox_node->appendChild( $cloned_container_node );
			$node->appendChild( $amp_lightbox_node );

			// Unwrap original container content out of "wp-block-navigation__responsive-close" and "wp-block-navigation__responsive-dialog" wrappers.
			$content_node = $this->dom->xpath->query( $this->generate_class_query( 'wp-block-navigation__responsive-container-content' ), $container_node )->item( 0 );
			$close_node   = $this->dom->xpath->query( $this->generate_class_query( 'wp-block-navigation__responsive-close' ), $container_node )->item( 0 );

			if ( $content_node instanceof DOMElement && $close_node instanceof DOMElement ) {
				$content_node->removeAttribute( Attribute::ID );

				$container_node->appendChild( $content_node );
				$container_node->removeChild( $close_node );
			}

			// Extend "open" and "close" buttons.
			$open_button_node  = $this->dom->xpath->query( $this->generate_class_query( 'wp-block-navigation__responsive-container-open', 'button' ), $node )->item( 0 );
			$close_button_node = $this->dom->xpath->query( $this->generate_class_query( 'wp-block-navigation__responsive-container-close', 'button' ), $node )->item( 0 );

			if ( $open_button_node instanceof DOMElement && $close_button_node instanceof DOMElement ) {
				$open_button_node->setAttribute( 'on', sprintf( 'tap:%s.open', $element_id ) );
				$close_button_node->setAttribute( 'on', sprintf( 'tap:%s.close', $element_id ) );
			}

			// Remove unwanted attributes.
			$unwanted_attributes = [
				'aria-expanded',
				'aria-modal',
				'data-micromodal-trigger',
				'data-micromodal-close',
			];

			foreach ( $unwanted_attributes as $unwanted_attribute ) {
				$items = $this->dom->xpath->query( sprintf( '//*[@%s]', $unwanted_attribute ), $node );
				foreach ( $items as $item ) {
					if ( ! $item instanceof DOMElement ) {
						continue;
					}
					$item->removeAttribute( $unwanted_attribute );
				}
			}
		}
	}

	/**
	 * Generate class query
	 *
	 * @since 2.2
	 *
	 * @param string $class_name Class name to use in query.
	 * @param string $tag        Tag to use in query.
	 *
	 * @return string Class query.
	 */
	private function generate_class_query( $class_name, $tag = 'div' ) {
		return sprintf(
			'//%1$s[ contains( concat( " ", normalize-space( @class ), " " ), " %2$s " ) ]',
			$tag,
			$class_name
		);
	}

	/**
	 * Dequeue uncompatible scripts during output buffering.
	 *
	 * @since 2.2
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = [] ) {
		add_action( 'wp_print_scripts', [ get_class(), 'dequeue_block_navigation_view_script' ], 0 );
		add_action( 'wp_print_footer_scripts', [ get_class(), 'dequeue_block_navigation_view_script' ], 0 );
	}

	/**
	 * Dequeue wp-block-navigation-view script.
	 *
	 * @since 2.2
	 */
	public static function dequeue_block_navigation_view_script() {
		wp_dequeue_script( 'wp-block-navigation-view' );
	}
}
