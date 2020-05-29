<?php
/**
 * Tests for AMP Reader Themes.
 *
 * @package AMP
 * @since 1.6
 */

/**
 * Tests for reader themes.
 *
 * @group reader-themes
 *
 * @covers AMP_Reader_Themes
 */
class Test_AMP_Reader_Themes extends WP_UnitTestCase {
	/**
	 * Test instance.
	 *
	 * @var AMP_Reader_Themes
	 */
	private $reader_themes;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->reader_themes = new AMP_Reader_Themes();
	}

	/**
	 * Test for get_themes.
	 *
	 * @covers AMP_Reader_Themes::get_themes
	 * @covers AMP_Reader_Themes::get_default_supported_reader_themes
	 * @covers AMP_Reader_Themes::get_classic_mode
	 * @covers AMP_Reader_Themes::get_default_raw_reader_themes
	 */
	public function test_get_themes() {
		$themes = $this->reader_themes->get_themes();

		$this->assertEquals( 10, count( $themes ) );
		$this->assertEquals( 'classic', end( $themes )['slug'] );
	}

	/**
	 * Test for get_reader_theme.
	 *
	 * @covers AMP_Reader_Themes::get_reader_theme
	 */
	public function test_get_reader_theme() {
		$this->assertFalse( $this->reader_themes->get_reader_theme( 'some-theme' ) );
		$this->assertArrayHasKey( 'slug', $this->reader_themes->get_reader_theme( 'classic' ) );
	}

	/**
	 * Test for prepare_theme.
	 *
	 * @covers AMP_Reader_Themes::prepare_theme
	 */
	public function test_prepare_theme() {
		$prepared_theme = $this->reader_themes->prepare_theme(
			[
				'disallowed_key' => '',
				'name'           => 'Theme Name',
			]
		);

		$this->assertArrayNotHasKey( 'disallowed_key', $prepared_theme );
		$this->assertEquals( 'Theme Name', $prepared_theme['name'] );
		$this->assertEquals( '', $prepared_theme['slug'] );
	}

	/**
	 * Test for prepare_theme_availability.
	 *
	 * @covers AMP_Reader_Themes::prepare_theme_availability
	 * @covers AMP_Reader_Themes::get_can_install
	 * @covers AMP_Reader_Themes::get_current_theme_name
	 */
	public function test_prepare_theme_availability() {
		$prepared_theme = $this->reader_themes->prepare_theme_availability(
			[
				'name'         => 'Some Theme',
				'requires'     => '99.9',
				'requires_php' => '99.9',
				'slug'         => 'some-theme',
			]
		);

		$this->assertFalse( $prepared_theme['availability']['is_active'] );
		$this->assertFalse( $prepared_theme['availability']['is_compatible_wp'] );
		$this->assertFalse( $prepared_theme['availability']['is_compatible_php'] );
		$this->assertFalse( $prepared_theme['availability']['is_installed'] );

		$prepared_theme = $this->reader_themes->prepare_theme_availability(
			[
				'name'         => 'WordPress Default',
				'requires'     => '4.4',
				'requires_php' => '5.2',
				'slug'         => 'default',
			]
		);

		$this->assertTrue( $prepared_theme['availability']['is_active'] );
		$this->assertTrue( $prepared_theme['availability']['is_compatible_wp'] );
		$this->assertTrue( $prepared_theme['availability']['is_compatible_php'] );
		$this->assertTrue( $prepared_theme['availability']['is_installed'] );
		$this->assertTrue( $prepared_theme['availability']['can_install'] );
	}
}
