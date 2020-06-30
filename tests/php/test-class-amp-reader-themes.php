<?php
/**
 * Tests for AMP Reader Themes.
 *
 * @package AMP
 * @since 1.6
 */

use AmpProject\AmpWP\Tests\ThemesApiRequestMocking;

/**
 * Tests for reader themes.
 *
 * @group reader-themes
 *
 * @covers AMP_Reader_Themes
 */
class Test_AMP_Reader_Themes extends WP_UnitTestCase {

	use ThemesApiRequestMocking;

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

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		$this->add_reader_themes_request_filter();

		switch_theme( 'twentytwenty' );
		$this->reader_themes = new AMP_Reader_Themes();
	}

	/**
	 * Test for get_themes.
	 *
	 * @covers AMP_Reader_Themes::get_themes
	 * @covers AMP_Reader_Themes::get_default_reader_themes
	 * @covers AMP_Reader_Themes::get_classic_mode
	 * @covers AMP_Reader_Themes::get_default_raw_reader_themes
	 */
	public function test_get_themes() {
		$themes = $this->reader_themes->get_themes();

		$this->assertEquals( 'legacy', end( $themes )['slug'] );

		$keys = [
			'name',
			'slug',
			'preview_url',
			'screenshot_url',
			'homepage',
			'description',
			'requires',
			'requires_php',
			'availability',
		];
		foreach ( $themes as $theme ) {
			$this->assertEqualSets( $keys, array_keys( $theme ) );
		}
	}

	/**
	 * Test for get_reader_theme_by_slug.
	 *
	 * @covers AMP_Reader_Themes::get_reader_theme_by_slug
	 */
	public function test_get_reader_theme_by_slug() {
		$this->assertFalse( $this->reader_themes->get_reader_theme_by_slug( 'some-theme' ) );
		$this->assertArrayHasKey( 'slug', $this->reader_themes->get_reader_theme_by_slug( 'legacy' ) );
	}

	/**
	 * Provides test themes to test availability.
	 *
	 * @return array
	 */
	public function get_availability_test_themes() {
		$is_installed = static function ( $theme ) {
			return wp_get_theme( $theme )->exists();
		};

		return [
			'twentysixteen_from_wp_future'           => [
				$is_installed( 'twentysixteen' ) ? AMP_Reader_Themes::STATUS_INSTALLED : AMP_Reader_Themes::STATUS_NON_INSTALLABLE,
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '99.9',
					'requires_php' => '5.2',
					'slug'         => 'twentysixteen',
				],
			],
			'twentysixteen_from_php_future'          => [
				$is_installed( 'twentysixteen' ) ? AMP_Reader_Themes::STATUS_INSTALLED : AMP_Reader_Themes::STATUS_NON_INSTALLABLE,
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => '99.9',
					'slug'         => 'twentysixteen',
				],
			],
			'non_reader_theme'                       => [
				AMP_Reader_Themes::STATUS_NON_INSTALLABLE,
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'some-nondefault-theme',
				],
			],
			'twentytwelve_not_requiring_wp_version'  => [
				$is_installed( 'twentytwelve' ) ? AMP_Reader_Themes::STATUS_INSTALLED : AMP_Reader_Themes::STATUS_INSTALLABLE,
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'twentytwelve',
				],
			],
			'twentytwelve_not_requiring_php_version' => [
				$is_installed( 'twentysixteen' ) ? AMP_Reader_Themes::STATUS_INSTALLED : AMP_Reader_Themes::STATUS_INSTALLABLE,
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => false,
					'slug'         => 'twentysixteen',
				],
			],
			'twentytwenty_active'                    => [
				AMP_Reader_Themes::STATUS_ACTIVE,
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
	 *
	 * @param string $expected    Expected.
	 * @param bool   $can_install Can install.
	 * @param array  $theme       Theme.
	 */
	public function test_get_theme_availability( $expected, $can_install, $theme ) {
		$this->assertEquals( $expected, $this->reader_themes->get_theme_availability( $theme ) );
		$this->assertEquals( $can_install, $this->reader_themes->can_install_theme( $theme ) );
	}

	/**
	 * Tests for can_install_theme.
	 *
	 * @covers AMP_Reader_Themes::can_install_theme
	 */
	public function test_can_install_theme() {
		$installable_theme = [
			'name'         => 'Some Theme',
			'requires'     => false,
			'requires_php' => '5.2',
			'slug'         => 'twentytwelve',
		];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertFalse( $this->reader_themes->can_install_theme( $installable_theme ) );

		wp_set_current_user( 1 );
		$this->assertTrue( $this->reader_themes->can_install_theme( $installable_theme ) );
	}
}
