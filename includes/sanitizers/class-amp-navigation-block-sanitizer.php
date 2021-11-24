<?php
/**
 * Class AMP_Navigation_Block_Sanitizer.
 *
 * @package AMP
 */

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
	 *
	 * 1. "fake" the "open" state by adding is-menu-open has-modal-open classes to div.wp-block-navigation__responsive-container
	 * 2. add on="tap:menuX.open" to button.wp-block-navigation__responsive-container-open element
	 * 3. add on="tap:menu1.close" to button.wp-block-navigation__responsive-container-close element
	 * 4. wrap div.wp-block-navigation__responsive-container with <amp-lightbox id="menuX" layout="nodisplay">...</amp-lightbox>
	 * 5. remove data-micromodal-trigger and data-micromodal-close attributes
	 * 6. remove aria-expanded and aria-modal attributes
	 * 7. duplicate div.wp-block-navigation__responsive-container (original one, without extra classes) outside the amp-lightbox wrapper
	 * 8. dequeue the wp-block-navigation-view script (see AMP_Navigation_Block_Sanitizer::add_buffering_hooks method)
	 *
	 * @since 2.2
	 */
	public function sanitize() {
		$class_query = sprintf( 'contains( concat( " ", normalize-space( @class ), " " ), " %s " )', self::$class );
		$expr        = sprintf(
			'//%1$s[ %2$s ]',
			self::$tag,
			$class_query
		);
		$nodes       = $this->dom->xpath->query( $expr );

		foreach ( $nodes as $node ) {
			/** @var DOMElement $node */
		}
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
