<?php
/**
 * Tests for AMP_Nav_Menu_Toggle_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Dom\Options;
use AmpProject\Dom\Document;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Tests for AMP_Nav_Menu_Toggle_Sanitizer.
 *
 * @covers AMP_Nav_Menu_Toggle_Sanitizer
 */
class Test_AMP_Nav_Menu_Toggle_Sanitizer extends TestCase {

	/**
	 * Data for converter test.
	 *
	 * @return array Data.
	 */
	public function data_converter() {
		$container_id = 'nav-menu-container';
		$toggle_id    = 'nav-menu-toggle';

		$container = '<div id="' . esc_attr( $container_id ) . '" class="nav-menu-wrapper"></div>';
		$toggle    = '<button id="' . esc_attr( $toggle_id ) . '">Toggle</button>';
		$head      = '<head><meta charset="utf-8"></head>';

		$amp_state               = '<amp-state id="navMenuToggledOn"><script type="application/json">false</script></amp-state>';
		$amp_get_container_attrs = function( $class = '', $toggle_class = 'toggled-on' ) {
			if ( empty( $toggle_class ) ) {
				return '';
			}
			return ' data-amp-bind-class="&quot;' . $class . '&quot; + ( navMenuToggledOn ? &quot; ' . $toggle_class . '&quot; : \'\' )"';
		};
		$amp_get_toggle_attrs    = function( $class = '', $toggle_class = 'toggled-on' ) { // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Sniff has a bug.
			return ' on="tap:AMP.setState({ navMenuToggledOn: ! navMenuToggledOn })" aria-expanded="false" data-amp-bind-aria-expanded="navMenuToggledOn ? \'true\' : \'false\'"' . ( ! empty( $toggle_class ) ? ' data-amp-bind-class="&quot;' . $class . '&quot; + ( navMenuToggledOn ? &quot; ' . $toggle_class . '&quot; : \'\' )"' : '' );
		};

		return [
			'container_before_toggle' => [
				'<html><body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $amp_state . str_replace( '></div>', $amp_get_container_attrs( 'nav-menu-wrapper' ) . '></div>', $container ) . str_replace( '>Toggle', $amp_get_toggle_attrs() . '>Toggle', $toggle ) . '</body></html>',
				[
					'nav_container_id'           => $container_id,
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
					'menu_button_toggle_class'   => 'toggled-on',
				],
			],
			'toggle_before_container' => [
				'<html><body>' . $toggle . $container . '</body></html>',
				'<html>' . $head . '<body>' . str_replace( '>Toggle', $amp_get_toggle_attrs() . '>Toggle', $toggle ) . $amp_state . str_replace( '></div>', $amp_get_container_attrs( 'nav-menu-wrapper' ) . '></div>', $container ) . '</body></html>',
				[
					'nav_container_id'           => $container_id,
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
					'menu_button_toggle_class'   => 'toggled-on',
				],
			],
			'container_is_body'       => [
				'<html><body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body' . $amp_get_container_attrs( '', 'nav-menu-toggled-on' ) . '>' . $amp_state . $container . str_replace( '>Toggle', $amp_get_toggle_attrs( '', '' ) . '>Toggle', $toggle ) . '</body></html>',
				[
					'nav_container_xpath'        => '//body',
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'nav-menu-toggled-on',
				],
			],
			'container_is_html'       => [
				'<html><body>' . $container . $toggle . '</body></html>',
				'<html' . $amp_get_container_attrs( '', 'nav-menu-toggled-on' ) . '>' . $head . '<body>' . $amp_state . $container . str_replace( '>Toggle', $amp_get_toggle_attrs( '', '' ) . '>Toggle', $toggle ) . '</body></html>',
				[
					'nav_container_xpath'        => '//html',
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'nav-menu-toggled-on',
				],
			],
			'no_container_provided'   => [
				'<html><body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $container . '</body></html>',
				[
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
				],
			],
			'no_arguments_provided'   => [
				'<html><body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				[],
			],
		];
	}

	/**
	 * Tests the content converted by the sanitizer.
	 *
	 * @param string $source   Content.
	 * @param string $expected Expected content.
	 * @param array  $args     Theme support arguments for 'nav_menu_toggle'.
	 *
	 * @dataProvider data_converter
	 * @covers AMP_Nav_Menu_Toggle_Sanitizer::sanitize()
	 * @covers AMP_Nav_Menu_Toggle_Sanitizer::get_nav_container()
	 * @covers AMP_Nav_Menu_Toggle_Sanitizer::get_menu_button()
	 */
	public function test_converter( $source, $expected, $args = [] ) {
		$dom       = Document::fromHtml( $source, Options::DEFAULTS );
		$sanitizer = new AMP_Nav_Menu_Toggle_Sanitizer( $dom, $args );

		$sanitizer->sanitize();

		$content = $dom->saveHTML( $dom->documentElement );

		$this->assertEquals( $expected, $content );
	}
}
