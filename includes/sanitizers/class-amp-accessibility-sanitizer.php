<?php
/**
 * Class AMP_Accessibility_Sanitizer.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Html\Role;
use AmpProject\Html\Tag;

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
		$this->add_skip_link();
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

	/**
	 * Add skip link markup and style.
	 *
	 * This is the implementation of the non-AMP logic in `the_block_template_skip_link()` which is unhooked in
	 * `AMP_Theme_Support::add_hooks()` to prevent validation errors from being raised.
	 *
	 * @see AMP_Theme_Support::add_hooks()
	 * @see the_block_template_skip_link()
	 * @return void
	 */
	public function add_skip_link() {

		// Early exit if not a block theme.
		if ( ! current_theme_supports( 'block-templates' ) ) {
			return;
		}

		// Early exit if not a block template.
		global $_wp_current_template_content;
		if ( ! $_wp_current_template_content ) {
			return;
		}

		$main_tag = $this->dom->getElementsByTagName( Tag::MAIN )->item( 0 );
		if ( ! $main_tag instanceof Element ) {
			return;
		}

		$skip_link_target = $this->dom->getElementId( $main_tag, 'wp--skip-link--target' );

		// Style for skip link.
		$style_content = '
			.skip-link.screen-reader-text {
				border: 0;
				clip: rect(1px,1px,1px,1px);
				clip-path: inset(50%);
				height: 1px;
				margin: -1px;
				overflow: hidden;
				padding: 0;
				position: absolute !important;
				width: 1px;
				word-wrap: normal !important;
			}

			.skip-link.screen-reader-text:focus {
				background-color: #eee;
				clip: auto !important;
				clip-path: none;
				color: #444;
				display: block;
				font-size: 1em;
				height: auto;
				left: 5px;
				line-height: normal;
				padding: 15px 23px 14px;
				text-decoration: none;
				top: 5px;
				width: auto;
				z-index: 100000;
			}
		';

		$style_node = AMP_DOM_Utils::create_node(
			$this->dom,
			Tag::STYLE,
			[
				Attribute::ID => 'amp-skip-link-styles',
			]
		);

		$style_node->appendChild( $this->dom->createTextNode( $style_content ) );

		// Skip link node.
		$skip_link = AMP_DOM_Utils::create_node(
			$this->dom,
			Tag::A,
			[
				Attribute::CLASS_ => 'skip-link screen-reader-text',
				Attribute::HREF   => "#$skip_link_target",
			]
		);

		$skip_link->appendChild( $this->dom->createTextNode( __( 'Skip to content', 'amp' ) ) );

		$body = $this->dom->body;
		$body->insertBefore( $skip_link, $body->firstChild );
		$body->insertBefore( $style_node, $body->firstChild );
	}
}
