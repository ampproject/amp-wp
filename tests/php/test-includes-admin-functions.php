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

	/** @covers ::amp_admin_get_preview_permalink() */
	public function test_amp_admin_get_preview_permalink() {
		$page_id = self::factory()->post->create(
			[
				'post_type' => 'page',
				'post_date' => '2020-01-01',
			]
		);
		$post_id = self::factory()->post->create(
			[
				'post_type' => 'post',
				'post_date' => '2021-01-01',
			]
		);

		// Default case.
		$this->assertEquals( amp_get_permalink( $post_id ), amp_admin_get_preview_permalink() );

		// Only get a page if it is the only supported post type.
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, [ 'page' ] );
		add_filter(
			'amp_customizer_post_type',
			static function () {
				return 'page';
			}
		);
		$this->assertEquals( amp_get_permalink( $page_id ), amp_admin_get_preview_permalink() );

		// Nothing returned if supported post types exist.
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, [ 'book' ] );
		$this->assertNull( amp_admin_get_preview_permalink() );

		// If is_singular is not a supported template, then no URL will be returned (currently).
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT           => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::SUPPORTED_POST_TYPES    => [ 'post', 'page' ],
				Option::SUPPORTED_TEMPLATES     => [ 'is_archive' ],
				Option::ALL_TEMPLATES_SUPPORTED => false,
			]
		);
		$this->assertNull( amp_admin_get_preview_permalink() );
	}

	/** @return array */
	public function get_data_for_test_amp_get_customizer_url() {
		return [
			'legacy_reader' => [
				'options' => [
					Option::THEME_SUPPORT => AMP_Theme_Support::READER_MODE_SLUG,
					Option::READER_THEME  => ReaderThemes::DEFAULT_READER_THEME,
				],
				function () {
					$this->assertTrue( amp_is_legacy() );
					$this->assertEquals( 'customize.php?amp_preview=1&autofocus[panel]=amp_panel', amp_get_customizer_url() );

					add_filter( 'amp_customizer_is_enabled', '__return_false' );
					$this->assertEquals( '', amp_get_customizer_url() );
				},
			],
			'reader_theme'  => [
				'options' => [
					Option::THEME_SUPPORT => AMP_Theme_Support::READER_MODE_SLUG,
					Option::READER_THEME  => 'twentynineteen',
				],
				function () {
					$this->assertFalse( amp_is_legacy() );
					$this->assertEquals( 'customize.php?amp_preview=1&amp=1', amp_get_customizer_url() );
				},
			],
			'transitional'  => [
				'options' => [
					Option::THEME_SUPPORT => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				],
				function () {
					$this->assertEquals( '', amp_get_customizer_url() );
				},
			],
		];
	}

	/**
	 * @dataProvider get_data_for_test_amp_get_customizer_url
	 * @covers ::amp_get_customizer_url()
	 *
	 * @param array    $options Options.
	 * @param callable $assert  Assert.
	 */
	public function test_amp_get_customizer_url( $options, $assert ) {
		AMP_Options_Manager::update_options( $options );
		$assert();
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
		$this->assertEquals( 'customize.php?amp_preview=1&amp=1', $submenu['themes.php'][0][2] );
	}
}
