<?php
/**
 * Test admin include functions in includes/admin/functions.php
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;

/**
 * Class Test_AMP_Admin_Includes_Functions
 */
class Test_AMP_Admin_Includes_Functions extends WP_UnitTestCase {

	use LoadsCoreThemes;

	public function setUp() {
		parent::setUp();
		remove_all_actions( 'amp_init' );
		remove_all_actions( 'admin_menu' );
		remove_all_actions( 'customize_register' );

		$this->register_core_themes();
	}

	public function tearDown() {
		parent::tearDown();

		$this->restore_theme_directories();

		unset(
			$GLOBALS['submenu'],
			$GLOBALS['menu']
		);
	}

	/** @covers ::amp_init_customizer() */
	public function test_amp_init_customizer_legacy_reader() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, ReaderThemes::DEFAULT_READER_THEME );
		amp_init_customizer();
		$this->assertTrue( amp_is_legacy() );
		$this->assertEquals( 500, has_action( 'customize_register', [ 'AMP_Template_Customizer', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'amp_init', [ 'AMP_Customizer_Design_Settings', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'admin_menu', 'amp_add_customizer_link' ) );
	}

	/** @covers ::amp_init_customizer() */
	public function test_amp_init_customizer_modern_reader() {
		switch_theme( 'twentytwenty' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, 'twentyseventeen' );
		$this->assertFalse( amp_is_legacy() );
		amp_init_customizer();
		$this->assertEquals( 500, has_action( 'customize_register', [ 'AMP_Template_Customizer', 'init' ] ) );
		$this->assertFalse( has_action( 'amp_init', [ 'AMP_Customizer_Design_Settings', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'admin_menu', 'amp_add_customizer_link' ) );
	}

	/** @covers ::amp_init_customizer() */
	public function test_amp_init_customizer_canonical() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		amp_init_customizer();
		$this->assertTrue( amp_is_canonical() );
		$this->assertFalse( amp_is_legacy() );
		$this->assertEquals( 500, has_action( 'customize_register', [ 'AMP_Template_Customizer', 'init' ] ) );
		$this->assertFalse( has_action( 'amp_init', [ 'AMP_Customizer_Design_Settings', 'init' ] ) );
		$this->assertFalse( has_action( 'admin_menu', 'amp_add_customizer_link' ) );
	}

	/** @covers ::amp_add_customizer_link() */
	public function test_amp_add_customizer_link_legacy() {
		global $submenu;
		$submenu = [];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( amp_is_legacy() );
		amp_add_customizer_link();

		$this->assertArrayHasKey( 'themes.php', $submenu );
		$this->assertEquals( 'AMP', $submenu['themes.php'][0][0] );
	}

	/** @covers ::amp_add_customizer_link() */
	public function test_amp_add_customizer_link_legacy_disabled() {
		global $submenu;
		$submenu = [];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( amp_is_legacy() );
		add_filter( 'amp_customizer_is_enabled', '__return_false' );
		amp_add_customizer_link();

		$this->assertEmpty( $submenu );
	}

	/** @covers ::amp_add_customizer_link() */
	public function test_amp_add_customizer_link_reader_theme() {
		global $submenu;
		$submenu = [];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, 'twentyseventeen' );
		$this->assertFalse( amp_is_legacy() );
		add_filter( 'amp_customizer_is_enabled', '__return_false' ); // This will be ignored.
		amp_add_customizer_link();

		$this->assertArrayHasKey( 'themes.php', $submenu );
		$this->assertEquals( 'AMP', $submenu['themes.php'][0][0] );
	}
}
