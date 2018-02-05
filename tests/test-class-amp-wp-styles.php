<?php
/**
 * Tests for AMP_WP_Styles.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Tests for AMP_WP_Styles.
 *
 * @covers AMP_WP_Styles
 */
class Test_AMP_WP_Styles extends WP_UnitTestCase {

	/**
	 * Tear down.
	 */
	public function tearDown() {
		global $wp_styles;
		parent::tearDown();
		$wp_styles = null;
	}

	/**
	 * Test that wp_styles() returns AMP_WP_Styles.
	 *
	 * @covers wp_styles()
	 */
	public function test_wp_styles() {
		AMP_Theme_Support::override_wp_styles();
		$this->assertInstanceOf( 'AMP_WP_Styles', wp_styles() );
	}

	/**
	 * Return bad URL.
	 *
	 * @return string Bad URL.
	 */
	public function return_bad_style_loader_src() {
		return site_url( 'wp-config.php' );
	}

	/**
	 * Tests get_validated_css_file_path.
	 *
	 * @covers AMP_WP_Styles::get_validated_css_file_path()
	 */
	public function test_get_validated_css_file_path() {
		$wp_styles = AMP_Theme_Support::override_wp_styles();

		// Theme.
		$expected = WP_CONTENT_DIR . '/themes/twentyseventeen/style.css';
		$path     = $wp_styles->get_validated_css_file_path( '/wp-content/themes/twentyseventeen/style.css', 'twentyseventeen-style' );
		$this->assertEquals( $expected, $path );
		$path = $wp_styles->get_validated_css_file_path( content_url( 'themes/twentyseventeen/style.css' ), 'dashicons' );
		$this->assertEquals( $expected, $path );

		add_filter( 'style_loader_src', array( $this, 'return_bad_style_loader_src' ) );
		$r = $wp_styles->get_validated_css_file_path( content_url( 'themes/twentyseventeen/style.css' ), 'dashicons' );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'amp_css_bad_file_extension', $r->get_error_code() );
		remove_filter( 'style_loader_src', array( $this, 'return_bad_style_loader_src' ) );

		// Includes.
		$expected = ABSPATH . WPINC . '/css/dashicons.css';
		$path     = $wp_styles->get_validated_css_file_path( '/wp-includes/css/dashicons.css', 'dashicons' );
		$this->assertEquals( $expected, $path );
		$path = $wp_styles->get_validated_css_file_path( includes_url( 'css/dashicons.css' ), 'dashicons' );
		$this->assertEquals( $expected, $path );

		// Admin.
		$expected = ABSPATH . 'wp-admin/css/common.css';
		$path     = $wp_styles->get_validated_css_file_path( '/wp-admin/css/common.css', 'dashicons' );
		$this->assertEquals( $expected, $path );
		$path = $wp_styles->get_validated_css_file_path( admin_url( 'css/common.css' ), 'common' );
		$this->assertEquals( $expected, $path );

		// Bad URLs.
		$r = $wp_styles->get_validated_css_file_path( content_url( 'themes/twentyseventeen/index.php' ), 'bad' );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'amp_css_bad_file_extension', $r->get_error_code() );

		$r = $wp_styles->get_validated_css_file_path( content_url( 'themes/twentyseventeen/404.css' ), 'bad' );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'amp_css_path_not_found', $r->get_error_code() );

		$r = $wp_styles->get_validated_css_file_path( get_template_directory() . '/style.css', 'bad' );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'amp_css_path_not_found', $r->get_error_code() );
	}

	/**
	 * Tests test_do_item.
	 *
	 * @covers AMP_WP_Styles::do_item()
	 */
	public function test_do_item() {
		$wp_styles = AMP_Theme_Support::override_wp_styles();
		$this->assertFalse( $wp_styles->do_item( 'non-existent' ) );

		// Conditional stylesheets are ignored.
		$wp_styles->registered['buttons-conditional']                       = clone $wp_styles->registered['buttons'];
		$wp_styles->registered['buttons-conditional']->extra['conditional'] = 'IE8';
		$this->assertFalse( $wp_styles->do_item( 'buttons-conditional' ) );

		// Alt stylesheets are ignored.
		$wp_styles->registered['buttons-alt']               = clone $wp_styles->registered['buttons'];
		$wp_styles->registered['buttons-alt']->extra['alt'] = true;
		$this->assertFalse( $wp_styles->do_item( 'buttons-alt' ) );

		// Media.
		$wp_styles->registered['admin-bar-print']       = clone $wp_styles->registered['admin-bar'];
		$wp_styles->registered['admin-bar-print']->args = 'x_virtual_reality';
		$wp_styles->print_code                          = '';
		$this->assertTrue( $wp_styles->do_item( 'admin-bar-print' ) );
		$this->assertStringStartsWith( '@media x_virtual_reality {', $wp_styles->print_code );

		// Inline style.
		$wp_styles->print_code = '';
		$inline                = '/* INLINE STYLE FOR BUTTONS */';
		wp_add_inline_style( 'buttons', $inline );
		$this->assertTrue( $wp_styles->do_item( 'buttons' ) );
		$wp_styles->do_item( 'buttons' );
		$this->assertStringEndsWith( $inline, $wp_styles->print_code );
		$this->assertContains( '.wp-core-ui .button-link', $wp_styles->print_code );

		// Buttons bad.
		$this->setExpectedException( 'PHPUnit_Framework_Error_Warning', 'Skipped stylesheet buttons-bad which does not have recognized CSS file extension (http://example.org/wp-config.php).' );
		$wp_styles->base_url                       = 'http://example.org';
		$wp_styles->print_code                     = '';
		$wp_styles->registered['buttons-bad']      = clone $wp_styles->registered['buttons'];
		$wp_styles->registered['buttons-bad']->src = $this->return_bad_style_loader_src();
		$this->assertFalse( $wp_styles->do_item( 'buttons-bad' ) );
		$this->assertEmpty( $wp_styles->print_code );
	}

	/**
	 * Tests test_do_item for stylesheets.
	 *
	 * @covers AMP_WP_Styles::do_item()
	 */
	public function test_do_item_font_stylesheet() {
		$wp_styles = AMP_Theme_Support::override_wp_styles();

		$ok_styles = array(
			'tangerine'   => 'https://fonts.googleapis.com/css?family=Tangerine',
			'typekit'     => 'https://use.typekit.net/abc.css',
			'fontscom'    => 'https://fast.fonts.net/abc.css',
			'fontawesome' => 'https://maxcdn.bootstrapcdn.com/font-awesome/123/css/font-awesome.min.css',
		);
		foreach ( $ok_styles as $handle => $src ) {
			$wp_styles->add( $handle, $src );
			ob_start();
			$this->assertTrue( $wp_styles->do_item( $handle ) );
			$output = ob_get_clean();
			$this->assertContains( '<link', $output );
			$this->assertContains( $src, $output );
		}

		$this->setExpectedException( 'PHPUnit_Framework_Error_Warning', 'Unable to locate filesystem path for stylesheet fontillegal (https://illegal.example.com/forbidden.css).' );
		$handle = 'fontillegal';
		$wp_styles->add( $handle, 'https://illegal.example.com/forbidden.css' );
		$this->assertFalse( $wp_styles->do_item( $handle ) );
	}

	/**
	 * Tests do_locale_stylesheet.
	 *
	 * @covers AMP_WP_Styles::do_locale_stylesheet()
	 */
	public function test_do_locale_stylesheet() {
		$wp_styles = AMP_Theme_Support::override_wp_styles();
		add_filter( 'locale_stylesheet_uri', '__return_false' );
		$this->assertFalse( $wp_styles->do_locale_stylesheet() );
		$this->assertEmpty( $wp_styles->print_code );
		remove_filter( 'locale_stylesheet_uri', '__return_false' );

		add_filter( 'locale_stylesheet_uri', array( $this, 'return_css_url' ) );
		$this->assertTrue( $wp_styles->do_locale_stylesheet() );
		$this->assertNotEmpty( $wp_styles->print_code );
	}

	/**
	 * Tests do_custom_css.
	 *
	 * @covers AMP_WP_Styles::do_custom_css()
	 */
	public function test_do_custom_css() {
		$wp_styles = AMP_Theme_Support::override_wp_styles();
		$this->assertFalse( $wp_styles->do_custom_css() );
		$this->assertEmpty( $wp_styles->print_code );

		add_filter( 'wp_get_custom_css', array( $this, 'return_css_rule' ) );
		$wp_styles = null;
		$wp_styles = AMP_Theme_Support::override_wp_styles();
		$wp_styles->do_custom_css();
		$this->assertEquals( $this->return_css_rule(), $wp_styles->print_code );
		$wp_styles->do_custom_css();
		$this->assertEquals( $this->return_css_rule(), $wp_styles->print_code );
		remove_filter( 'wp_get_custom_css', array( $this, 'return_css_rule' ) );
	}

	/**
	 * Return sample CSS rule.
	 *
	 * @return string
	 */
	public function return_css_rule() {
		return 'body { color:black; }';
	}

	/**
	 * Return URL to valid CSS file.
	 *
	 * @return string
	 */
	public function return_css_url() {
		return includes_url( 'css/buttons.css' );
	}
}
