<?php
/**
 * Tests for AMP_Nav_Menu_Toggle_Sanitizer.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Nav_Menu_Toggle_Sanitizer.
 *
 * @covers AMP_Nav_Menu_Toggle_Sanitizer
 * @group testtt
 */
class Test_AMP_Nav_Menu_Toggle_Sanitizer extends WP_UnitTestCase {

	/**
	 * Data for converter test.
	 *
	 * @return array Data.
	 */
	public function data_converter() {
		$container_id = 'nav-menu-container';
		$toggle_id    = 'nav-menu-toggle';

		$head      = sprintf( '<head><meta http-equiv="content-type" content="text/html; charset=%s"></head>', get_bloginfo( 'charset' ) );
		$container = '<div id="' . esc_attr( $container_id ) . '" class="nav-menu-wrapper"></div>';
		$toggle    = '<button id="' . esc_attr( $toggle_id ) . '">Toggle</button>';

		$amp_state               = '<amp-state id="navMenuToggledOn"><script type="application/json">false</script></amp-state>';
		$amp_get_container_attrs = function( $class = '', $toggle_class = 'toggled-on' ) {
			if ( empty( $toggle_class ) ) {
				return '';
			}
			return ' [class]="&quot;' . $class . '&quot; + ( navMenuToggledOn ? &quot; ' . $toggle_class . '&quot; : \'\' )"';
		};
		$amp_get_toggle_attrs    = function( $class = '', $toggle_class = 'toggled-on' ) {
			return ' on="tap:AMP.setState({ navMenuToggledOn: ! navMenuToggledOn })" aria-expanded="false" [aria-expanded]="navMenuToggledOn ? \'true\' : \'false\'"' . ( ! empty( $toggle_class ) ? ' [class]="&quot;' . $class . '&quot; + ( navMenuToggledOn ? &quot; ' . $toggle_class . '&quot; : \'\' )"' : '' );
		};

		return array(
			'container_before_toggle' => array(
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $amp_state . str_replace( '></div>', $amp_get_container_attrs( 'nav-menu-wrapper' ) . '></div>', $container ) . str_replace( '>Toggle', $amp_get_toggle_attrs() . '>Toggle', $toggle ) . '</body></html>',
				array(
					'nav_container_id'           => $container_id,
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
					'menu_button_toggle_class'   => 'toggled-on',
				),
			),
			'toggle_before_container' => array(
				'<html>' . $head . '<body>' . $toggle . $container . '</body></html>',
				'<html>' . $head . '<body>' . str_replace( '>Toggle', $amp_get_toggle_attrs() . '>Toggle', $toggle ) . $amp_state . str_replace( '></div>', $amp_get_container_attrs( 'nav-menu-wrapper' ) . '></div>', $container ) . '</body></html>',
				array(
					'nav_container_id'           => $container_id,
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
					'menu_button_toggle_class'   => 'toggled-on',
				),
			),
			'container_is_body'       => array(
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body' . $amp_get_container_attrs( '', 'nav-menu-toggled-on' ) . '>' . $amp_state . $container . str_replace( '>Toggle', $amp_get_toggle_attrs( '', '' ) . '>Toggle', $toggle ) . '</body></html>',
				array(
					'nav_container_xpath'        => '//body',
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'nav-menu-toggled-on',
				),
			),
			'container_is_html'       => array(
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				'<html' . $amp_get_container_attrs( '', 'nav-menu-toggled-on' ) . '>' . $head . '<body>' . $amp_state . $container . str_replace( '>Toggle', $amp_get_toggle_attrs( '', '' ) . '>Toggle', $toggle ) . '</body></html>',
				array(
					'nav_container_xpath'        => '//html',
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'nav-menu-toggled-on',
				),
			),
			'no_container_provided'   => array(
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $container . '</body></html>',
				array(
					'menu_button_id'             => $toggle_id,
					'nav_container_toggle_class' => 'toggled-on',
				),
			),
			'no_arguments_provided'   => array(
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				'<html>' . $head . '<body>' . $container . $toggle . '</body></html>',
				array(),
			),
		);
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
	public function test_converter( $source, $expected, $args = array() ) {
		$dom       = AMP_DOM_Utils::get_dom( $source );
		$sanitizer = new AMP_Nav_Menu_Toggle_Sanitizer( $dom, $args );

		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		$this->assertEquals( $expected, $content );
	}
}
