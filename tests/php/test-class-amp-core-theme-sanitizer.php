<?php
/**
 * Class AMP_Core_Theme_Sanitizer_Test.
 *
 * @package AMP
 */

use Amp\AmpWP\Dom\Document;
use Amp\AmpWP\Tests\PrivateAccess;

/**
 * Class AMP_Core_Theme_Sanitizer_Test
 */
class AMP_Core_Theme_Sanitizer_Test extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Data for testing the conversion of a CSS selector to a XPath.
	 *
	 * @return array
	 */
	public function get_xpath_from_css_selector_data() {
		return [
			// Single element.
			[ 'body', '//body' ],
			// Simple ID.
			[ '#some-id', "//*[ @id = 'some-id' ]" ],
			// Simple class.
			[ '.some-class', "//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' some-class ' ) ]" ],
			// Class descendants.
			[ '.some-class .other-class', "//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' some-class ' ) ]//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' other-class ' ) ]" ],
			// Class direct descendants.
			[ '.some-class > .other-class', "//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' some-class ' ) ]/*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' other-class ' ) ]" ],
			// ID direct descendant elements.
			[ '#some-id > ul', "//*[ @id = 'some-id' ]/ul" ],
			// ID direct descendant elements with messy whitespace.
			[ "   \t  \n #some-id    \t  >   \n  ul  \t \n ", "//*[ @id = 'some-id' ]/ul" ],
		];
	}

	/**
	 * Test xpath_from_css_selector().
	 *
	 * @dataProvider get_xpath_from_css_selector_data
	 * @covers AMP_Core_Theme_Sanitizer::xpath_from_css_selector()
	 *
	 * @param string $css_selector CSS Selector.
	 * @param string $expected     Expected XPath expression.
	 */
	public function test_xpath_from_css_selector( $css_selector, $expected ) {
		$dom       = new Document();
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );
		$actual    = $this->call_private_method( $sanitizer, 'xpath_from_css_selector', [ $css_selector ] );
		$this->assertEquals( $expected, $actual );
	}

	public function get_get_closest_submenu_data() {
		$html = '
			<nav>
				<ul class="primary-menu">
					<li id="menu-item-1" class="menu-item menu-item-1"><a href="https://example.com/a">Link A</a></li>
					<li id="menu-item-2" class="menu-item menu-item-2"><a href="https://example.com/b">Link B</a><span class="icon"></span>
						<ul id="sub-menu-1" class="sub-menu">
							<li id="menu-item-3" class="menu-item menu-item-3"><a href="https://example.com/c">Link C</a></li>
							<li id="menu-item-4" class="menu-item menu-item-4"><a href="https://example.com/d">Link D</a></li>
						</ul>
					</li>
					<li id="menu-item-5" class="menu-item menu-item-5"><a href="https://example.com/e">Link E</a><span class="icon"></span>
						<ul id="sub-menu-2" class="sub-menu">
							<li id="menu-item-6" class="menu-item menu-item-6"><a href="https://example.com/f">Link F</a><span class="icon"></span>
								<ul id="sub-menu-3" class="sub-menu">
									<li id="menu-item-7" class="menu-item menu-item-7"><a href="https://example.com/g">Link G</a></li>
									<li id="menu-item-8" class="menu-item menu-item-8"><a href="https://example.com/h">Link H</a></li>
								</ul>
							</li>
							<li id="menu-item-9" class="menu-item menu-item-9"><a href="https://example.com/i">Link I</a></li>
						</ul>
					</li>
				</ul>
			</nav>
		';
		$dom  = AMP_DOM_Utils::get_dom_from_content( $html );
		return [
			// First sub-menu.
			[ $dom, $dom->xpath->query( "//*[ @id = 'menu-item-2' ]" )->item( 0 ), $dom->xpath->query( "//*[ @id = 'sub-menu-1' ]" )->item( 0 ) ],

			// Second sub-menu.
			[ $dom, $dom->xpath->query( "//*[ @id = 'menu-item-5' ]" )->item( 0 ), $dom->xpath->query( "//*[ @id = 'sub-menu-2' ]" )->item( 0 ) ],

			// Sub-menu of second sub-menu.
			[ $dom, $dom->xpath->query( "//*[ @id = 'menu-item-6' ]" )->item( 0 ), $dom->xpath->query( "//*[ @id = 'sub-menu-3' ]" )->item( 0 ) ],
		];
	}

	/**
	 * Test get_closest_submenu().
	 *
	 * @dataProvider get_get_closest_submenu_data
	 * @covers AMP_Core_Theme_Sanitizer::get_closest_submenu()
	 *
	 * @param Document   $dom      Document.
	 * @param DOMElement $element  Element.
	 * @param DOMElement $expected Expected element.
	 */
	public function test_get_closest_submenu( $dom, $element, $expected ) {
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );
		$actual    = $this->call_private_method( $sanitizer, 'get_closest_submenu', [ $element ] );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test get_supported_themes().
	 *
	 * @covers AMP_Core_Theme_Sanitizer::get_supported_themes()
	 */
	public function test_get_supported_themes() {
		$supported_themes = [
			'twentytwenty',
			'twentynineteen',
			'twentyseventeen',
			'twentysixteen',
			'twentyfifteen',
			'twentyfourteen',
			'twentythirteen',
			'twentytwelve',
			'twentyeleven',
			'twentyten',
		];

		$this->assertEquals( $supported_themes, AMP_Core_Theme_Sanitizer::get_supported_themes() );
	}

	/**
	 * Data for testing acceptable errors for supported themes.
	 *
	 * @return array
	 */
	public function get_templates() {
		$not_supported = [ 'foo', 'bar' ];

		$templates = array_merge( $not_supported, AMP_Core_Theme_Sanitizer::get_supported_themes() );

		return array_map(
			static function ( $template ) use ( $not_supported ) {
				if ( in_array( $template, $not_supported, true ) ) {
					$acceptable_errors = [];
				} else {
					$acceptable_errors = [
						AMP_Style_Sanitizer::CSS_SYNTAX_INVALID_AT_RULE => [
							[
								'at_rule' => 'viewport',
							],
							[
								'at_rule' => '-ms-viewport',
							],
						],
					];
				}

				return [ $template, $acceptable_errors ];
			},
			$templates
		);
	}

	/**
	 * Test get_acceptable_errors().
	 *
	 * @covers AMP_Core_Theme_Sanitizer::get_acceptable_errors()
	 *
	 * @dataProvider get_templates
	 *
	 * @param string $template Template name.
	 * @param array $expected Expected acceptable errors.
	 */
	public function test_get_acceptable_errors( $template, $expected ) {
		$actual = AMP_Core_Theme_Sanitizer::get_acceptable_errors( $template );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test add_has_header_video_body_class().
	 *
	 * @covers AMP_Core_Theme_Sanitizer::add_has_header_video_body_class()
	 */
	public function test_add_has_header_video_body_class() {
		$args = [ 'foo' ];

		// Without has_header_video().
		AMP_Core_Theme_Sanitizer::add_has_header_video_body_class( $args );

		$expected = [ 'foo' ];
		$actual   = apply_filters( 'body_class', $args );
		$this->assertEquals( $expected, $actual );

		// With has_header_video().
		remove_all_filters( 'body_class' );

		add_filter(
			'get_header_video_url',
			static function () {
				return 'https://example.com';
			}
		);

		AMP_Core_Theme_Sanitizer::add_has_header_video_body_class( $args );
		$expected = [ 'foo', 'has-header-video' ];
		$actual   = apply_filters( 'body_class', $args );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Data for testing guessing of modal roles.
	 *
	 * @return array
	 */
	public function get_modals() {
		$dom         = new Document();
		$modal_roles = $this->get_static_private_property( 'AMP_Core_Theme_Sanitizer', 'modal_roles' );

		$a = array_map(
			static function ( $rule ) use ( $dom ) {
				return [ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => $rule ] ), $rule ];
			},
			$modal_roles
		);

		return array_merge(
			$a,
			[
				[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'foo' => 'bar' ] ), 'dialog' ],
				[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'foo' ] ), 'dialog' ],
				[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'top_navigation' ] ), 'dialog' ],
				[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => ' a	search  c ' ] ), 'search' ],
			]
		);
	}

	/**
	 * Test guess_modal_role().
	 *
	 * @dataProvider get_modals
	 * @covers       AMP_Core_Theme_Sanitizer::guess_modal_role()
	 *
	 * @param DOMElement $dom_element Document.
	 * @param string     $expected    Expected.
	 * @throws ReflectionException
	 */
	public function test_guess_modal_role( DOMElement $dom_element, $expected ) {
		$sanitizer = new AMP_Core_Theme_Sanitizer( new Document() );
		$actual    = $this->call_private_method( $sanitizer, 'guess_modal_role', [ $dom_element ] );

		$this->assertEquals( $expected, $actual );
	}
}
