<?php
/**
 * Class AMP_Core_Theme_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\Dom\Document;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * Class AMP_Core_Theme_Sanitizer_Test
 *
 * @coversDefaultClass AMP_Core_Theme_Sanitizer
 */
class AMP_Core_Theme_Sanitizer_Test extends WP_UnitTestCase {

	use AssertContainsCompatibility;
	use PrivateAccess;
	use LoadsCoreThemes;

	public function setUp() {
		parent::setUp();

		$this->register_core_themes();
	}

	public function tearDown() {
		parent::tearDown();

		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		$this->restore_theme_directories();
	}

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
	 * @covers ::xpath_from_css_selector()
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
	 * @covers ::get_closest_submenu()
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
	 * @covers ::get_supported_themes()
	 */
	public function test_get_supported_themes() {
		$supported_themes = [
			'twentytwentyone',
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

	/** @covers ::dequeue_scripts() */
	public function test_dequeue_scripts() {
		$handle = 'foo';
		wp_enqueue_script( $handle, 'a/random/source', [], 'v1', true );

		AMP_Core_Theme_Sanitizer::dequeue_scripts( [ $handle ] );

		wp_enqueue_scripts();

		$this->assertFalse( wp_script_is( $handle ) );
	}

	/** @covers ::force_svg_support() */
	public function test_force_svg_support() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '' );
		$dom->documentElement->setAttribute( 'class', 'no-svg' );

		( new AMP_Core_Theme_Sanitizer( $dom ) )->force_svg_support();

		$this->assertEquals( ' svg ', $dom->documentElement->getAttribute( 'class' ) );
	}

	/** @covers ::force_fixed_background_support() */
	public function test_force_fixed_background_support() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '' );

		( new AMP_Core_Theme_Sanitizer( $dom ) )->force_fixed_background_support();

		$this->assertStringEndsWith( ' background-fixed', $dom->documentElement->getAttribute( 'class' ) );
	}

	/**
	 * Test extend_theme_support().
	 *
	 * @covers ::extend_theme_support()
	 */
	public function test_extend_theme_support() {
		$theme_dir = basename( dirname( AMP__DIR__ ) ) . '/' . basename( AMP__DIR__ ) . '/tests/php/data/themes';
		register_theme_directory( $theme_dir );

		// Make sure that theme support is added even when no special keys are needed.
		remove_theme_support( 'amp' );
		switch_theme( 'twentytwenty' );
		AMP_Core_Theme_Sanitizer::extend_theme_support();
		$this->assertTrue( current_theme_supports( 'amp' ) );
		$this->assertEquals(
			[ 'paired' => true ],
			AMP_Theme_Support::get_theme_support_args()
		);

		// Make sure the expected theme support is added for a core theme.
		remove_theme_support( 'amp' );
		switch_theme( 'twentysixteen' );
		AMP_Core_Theme_Sanitizer::extend_theme_support();
		$this->assertTrue( current_theme_supports( 'amp' ) );
		$this->assertEqualSets(
			[ 'paired', 'nav_menu_toggle', 'nav_menu_dropdown' ],
			array_keys( AMP_Theme_Support::get_theme_support_args() )
		);

		// Ensure custom themes do not get extended with theme support.
		remove_theme_support( 'amp' );
		$this->assertTrue( wp_get_theme( 'custom' )->exists() );
		switch_theme( 'custom' );
		AMP_Core_Theme_Sanitizer::extend_theme_support();
		$this->assertFalse( current_theme_supports( 'amp' ) );
		$this->assertFalse( AMP_Theme_Support::get_theme_support_args() );

		// Ensure that child theme inherits extended core theme support.
		$this->assertTrue( wp_get_theme( 'child-of-core' )->exists() );
		switch_theme( 'child-of-core' );
		AMP_Core_Theme_Sanitizer::extend_theme_support();
		$this->assertTrue( current_theme_supports( 'amp' ) );
		$this->assertEqualSets(
			[ 'paired', 'nav_menu_toggle', 'nav_menu_dropdown' ],
			array_keys( AMP_Theme_Support::get_theme_support_args() )
		);
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
	 * Test add_has_header_video_body_class().
	 *
	 * @covers ::add_has_header_video_body_class()
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
		$modal_roles = $this->get_private_property( 'AMP_Core_Theme_Sanitizer', 'modal_roles' );

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
	 * @covers       ::guess_modal_role()
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

	/**
	 * Tests add_img_display_block_fix.
	 *
	 * @covers ::add_img_display_block_fix()
	 */
	public function test_add_img_display_block_fix() {
		AMP_Core_Theme_Sanitizer::add_img_display_block_fix();
		ob_start();
		wp_print_styles();
		$output = ob_get_clean();
		$this->assertRegExp( '/amp-img.+display.+block/s', $output );
	}

	/**
	 * Tests add_twentytwenty_custom_logo_fix.
	 *
	 * @covers ::add_twentytwenty_custom_logo_fix()
	 */
	public function test_add_twentytwenty_custom_logo_fix() {
		add_filter(
			'get_custom_logo',
			static function () {
				return '<img src="https://example.com/logo.jpg" width="100" height="200">';
			}
		);

		AMP_Core_Theme_Sanitizer::add_twentytwenty_custom_logo_fix();
		$logo = get_custom_logo();

		$needle = '.site-logo amp-img { width: 3.000000rem; } @media (min-width: 700px) { .site-logo amp-img { width: 4.500000rem; } }';

		$this->assertStringContains( $needle, $logo );
	}

	/**
	 * Tests prevent_sanitize_in_customizer_preview.
	 *
	 * @covers ::prevent_sanitize_in_customizer_preview()
	 */
	public function test_prevent_sanitize_in_customizer_preview() {
		global $wp_customize;

		require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
		$wp_customize = new \WP_Customize_Manager();

		$xpath_selectors = [ '//p[ @id = "foo" ]' ];

		$html     = '<p id="foo"></p> <p id="bar"></p>';
		$expected = '<p id="foo" data-ampdevmode=""></p> <p id="bar"></p>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $html );
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );

		$wp_customize->start_previewing_theme();
		$sanitizer->prevent_sanitize_in_customizer_preview( $xpath_selectors );
		$wp_customize->stop_previewing_theme();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

	/** @covers ::add_twentyfourteen_search() */
	public function test_add_twentyfourteen_search() {
		$html = '
			<div class="search-toggle">
				<a href="#"></a>
			</div>
			<div id="search-container">
				<input type="text" name="s">
			</div>
		';

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		( new AMP_Core_Theme_Sanitizer( $dom ) )->add_twentyfourteen_search();

		$this->assertEquals( 1, $dom->xpath->query( '//div[ @id = "search-container" and @data-amp-bind-class and ./amp-state[ ./script ] ]' )->length );
		$this->assertEquals( 1, $dom->xpath->query( '//a[ not( @href ) and @on and @tabindex and @role and @aria-expanded and @data-amp-bind-aria-expanded ]' )->length );
		$this->assertEquals( 1, $dom->xpath->query( '//div[ @class = "search-toggle" and @data-amp-bind-class ]' )->length );
	}

	/**
	 * Tests amend_twentytwentyone_sub_menu_toggles.
	 *
	 * @covers ::amend_twentytwentyone_sub_menu_toggles()
	 */
	public function test_amend_twentytwentyone_sub_menu_toggles() {
		$html = '
			<ul id="primary-menu-list">
				<div>
					<button onclick="twentytwentyoneExpandSubMenu(this)"></button>
				</div>
			</ul>
			<ul class="footer-navigation-wrapper foo">
				<div>
					<button onclick="twentytwentyoneExpandSubMenu(this)"></button>
				</div>
			</ul>
			<button onclick="foo"></button>
		';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $html );
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );

		$sanitizer->amend_twentytwentyone_sub_menu_toggles();
		$elements = $dom->xpath->query( '//*[ @onclick ]' );

		$this->assertEquals( 1, $elements->length );
	}

	/** @covers ::amend_twentytwentyone_dark_mode_styles() */
	public function test_amend_twentytwentyone_dark_mode_styles() {
		$theme_slug = 'twentytwentyone';
		if ( ! wp_get_theme( $theme_slug )->exists() ) {
			$this->markTestSkipped();
			return;
		}

		switch_theme( $theme_slug );
		wp_enqueue_style( 'tt1-dark-mode', get_theme_file_path( 'dark-mode.css' ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_style( 'twenty-twenty-one-style', get_theme_file_path( 'style.css' ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		$this->assertEmpty( wp_styles()->registered['twenty-twenty-one-style']->extra );
		AMP_Core_Theme_Sanitizer::amend_twentytwentyone_dark_mode_styles();
		wp_enqueue_scripts();

		$this->assertFalse( wp_style_is( 'tt1-dark-mode', 'enqueued' ) );
		$extra = wp_styles()->registered['twenty-twenty-one-style']->extra;
		$this->assertNotEmpty( $extra );
		$this->assertArrayHasKey( 'after', $extra );
		$after = implode( '', $extra['after'] );

		$replacements = [
			'@media only screen'           => '@media only screen and (prefers-color-scheme: dark)',
			'.is-dark-theme.is-dark-theme' => ':root',
			'.respect-color-scheme-preference.is-dark-theme body' => '.respect-color-scheme-preference body',
		];
		foreach ( $replacements as $search => $replacement ) {
			$this->assertStringNotContains( "$search {", $after );
			$this->assertStringContains( "$replacement {", $after );
		}
	}

	/** @covers ::amend_twentytwentyone_styles() */
	public function test_amend_twentytwentyone_styles() {
		$theme_slug = 'twentytwentyone';
		if ( ! wp_get_theme( $theme_slug )->exists() ) {
			$this->markTestSkipped();
			return;
		}

		switch_theme( $theme_slug );

		$style_handle = 'twenty-twenty-one-style';
		wp_enqueue_style( $style_handle, get_theme_file_path( 'style.css' ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		$this->assertEmpty( wp_styles()->registered[ $style_handle ]->extra );

		wp_add_inline_style( $style_handle, '/*first*/' );
		AMP_Core_Theme_Sanitizer::amend_twentytwentyone_styles();
		wp_enqueue_scripts();

		$after = implode( '', wp_styles()->registered[ $style_handle ]->extra['after'] );
		$this->assertNotEmpty( $after );
		$this->assertStringContains( '@media only screen and (max-width: 481px)', $after );
		$this->assertStringEndsWith( '/*first*/', $after );
	}

	/**
	 * Tests add_twentytwentyone_mobile_modal.
	 *
	 * @covers ::add_twentytwentyone_mobile_modal()
	 */
	public function test_add_twentytwentyone_mobile_modal() {
		$html = '
			<nav>
				<div class="menu-button-container">
					<button id="primary-mobile-menu">
						<span class="dropdown-icon open">Menu</span>
						<span class="dropdown-icon close">Close</span>
					</button>
				</div>
				<div class="primary-menu-container">
					<ul id="primary-menu-list">
						<li id="menu-item-1">Foo</li>
						<li id="menu-item-2">Bar</li>
						<li id="menu-item-3">Baz</li>
					</ul>
				</div>
			</nav>
		';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $html );
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );

		$sanitizer->add_twentytwentyone_mobile_modal();

		$query = $dom->xpath->query( '//button[ @id = "primary-mobile-menu" and @data-amp-bind-aria-expanded and @on ]' );

		$this->assertEquals( 1, $query->length );
	}

	/**
	 * Tests add_twentytwentyone_sub_menu_fix.
	 *
	 * @covers ::add_twentytwentyone_sub_menu_fix()
	 */
	public function test_add_twentytwentyone_sub_menu_fix() {
		$html = '
			<nav>
				<div class="menu-button-container">
					<button id="primary-mobile-menu">Menu button toggle</button>
				</div>
				<div class="primary-menu-container">
					<ul id="primary-menu-list">
						<li id="menu-item-1"><button>Foo</button></li>
						<li id="menu-item-2"><button class="sub-menu-toggle">Bar</button></li>
						<li id="menu-item-3"><button class="sub-menu-toggle">Baz</button></li>
					</ul>
				</div>
			</nav>
		';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $html );
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );

		$sanitizer->add_twentytwentyone_sub_menu_fix();

		$query = $dom->xpath->query( '//nav/div//button[ @data-amp-bind-aria-expanded ]' );
		$this->assertEquals( 2, $query->length );

		for ( $i = 1; $i <= $query->length; $i++ ) {
			/** @var DOMElement $menu_toggle */
			$menu_toggle = $query->item( $i - 1 );

			$toggle_id       = 'toggle_' . ( $i );
			$other_toggle_id = 'toggle_' . ( $i === $query->length ? $i - 1 : $i + 1 );

			$this->assertEquals( "{$toggle_id} ? 'true' : 'false'", $menu_toggle->getAttribute( 'data-amp-bind-aria-expanded' ) );
			$this->assertEquals( "tap:AMP.setState({{$toggle_id}:!{$toggle_id},{$other_toggle_id}:false})", $menu_toggle->getAttribute( 'on' ) );
		}

		$this->assertEquals( 'tap:AMP.setState({toggle_1:false,toggle_2:false})', $dom->body->getAttribute( 'on' ) );
	}

	/**
	 * Tests add_twentytwentyone_dark_mode_toggle.
	 *
	 * @covers ::add_twentytwentyone_dark_mode_toggle()
	 */
	public function test_add_twentytwentyone_dark_mode_toggle() {
		$html = '<button id="dark-mode-toggler">Toggle dark mode</button>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $html );
		$sanitizer = new AMP_Core_Theme_Sanitizer( $dom );

		$sanitizer->add_twentytwentyone_dark_mode_toggle();

		$this->assertEquals(
			'.no-js #dark-mode-toggler { display: block; }',
			$dom->head->getElementsByTagName( 'style' )->item( 0 )->textContent
		);

		$this->assertEquals(
			'<button id="dark-mode-toggler" on="tap:AMP.setState({is_dark_theme: !is_dark_theme}),i-amp-0.toggleClass(class=\'is-dark-theme\'),i-amp-1.toggleClass(class=\'is-dark-theme\')" data-amp-bind-aria-pressed="is_dark_theme ? \'true\' : \'false\'">Toggle dark mode</button>',
			$dom->saveHTML( $dom->getElementById( 'dark-mode-toggler' ) )
		);
	}
}
