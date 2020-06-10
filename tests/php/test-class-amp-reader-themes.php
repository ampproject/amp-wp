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

		switch_theme( 'twentytwenty' );
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
	 * Test for get_reader_theme_by_slug.
	 *
	 * @covers AMP_Reader_Themes::get_reader_theme_by_slug
	 */
	public function test_get_reader_theme_by_slug() {
		$this->assertFalse( $this->reader_themes->get_reader_theme_by_slug( 'some-theme' ) );
		$this->assertArrayHasKey( 'slug', $this->reader_themes->get_reader_theme_by_slug( 'classic' ) );
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
	 * Provides test themes to test availability.
	 *
	 * @return array
	 */
	public function get_availability_test_themes() {
		return [
			[
				'installed', // Is installed in CI environment.
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '99.9',
					'requires_php' => '5.2',
					'slug'         => 'twentysixteen',
				],
			],
			[
				'installed',  // Is installed in CI environment.
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => '99.9',
					'slug'         => 'twentysixteen',
				],
			],
			[
				'non-installable',
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'some-nondefault-theme',
				],
			],
			[
				'installed', // Is installed in CI environment.
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'twentytwelve',
				],
			],
			[
				'installed', // Is installed in CI environment.
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => false,
					'slug'         => 'twentysixteen',
				],
			],
			[
				'active', // Is installed in CI environment.
				true,
				[
					'name'         => 'WordPress Default',
					'requires'     => '4.4',
					'requires_php' => '5.2',
					'slug'         => 'twentytwenty',
				],
			],
		];
	}

	/**
	 * Test for get_theme_availability.
	 *
	 * @covers AMP_Reader_Themes::get_theme_availability
	 * @covers AMP_Reader_Themes::can_install_theme
	 *
	 * @dataProvider get_availability_test_themes
	 */
	public function test_get_theme_availability( $expected, $can_install, $theme ) {
		$this->assertEquals( $expected, $this->reader_themes->get_theme_availability( $theme ) );
		$this->assertEquals( $can_install, $this->reader_themes->can_install_theme( $theme ) );
	}
}
