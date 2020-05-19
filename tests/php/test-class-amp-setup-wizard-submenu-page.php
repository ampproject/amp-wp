<?php
/**
 * Tests for AMP_Setup_Wizard_Submenu_Page class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Setup_Wizard_Submenu_Page class.
 *
 * @group setup
 *
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 *
 * @covers AMP_Setup_Wizard_Submenu
 */
class Test_AMP_Setup_Wizard_Submenu_Page extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->old_wp_scripts = isset( $GLOBALS['wp_scripts'] ) ? $GLOBALS['wp_scripts'] : null;

		$this->page = new AMP_Setup_Wizard_Submenu_Page( 'amp-options' );
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		parent::tearDown();

		add_action( 'wp_default_scripts', 'wp_default_scripts' );

		if ( function_exists( 'wp_default_packages' ) ) {
			add_action( 'wp_default_scripts', 'wp_default_packages' );
		}

		$GLOBALS['wp_scripts'] = new WP_Scripts();
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::init
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::init
	 */
	public function test_init() {
		$this->page->init();

		$this->assertEquals( 99, has_action( 'admin_enqueue_scripts', [ $this->page, 'override_scripts' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->page, 'enqueue_assets' ] ) );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::render
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::render
	 */
	public function test_render() {
		ob_start();

		$this->page->render();

		$this->assertEquals( trim( ob_get_clean() ), '<div id="amp-setup"></div>' );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::screen_handle
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::screen_handle
	 */
	public function test_screen_handle() {
		$this->assertEquals( $this->page->screen_handle(), 'amp_page_amp-setup' );
	}

	/**
	 * Provides test data for test_add_setup_script.
	 *
	 * @return array
	 */
	public function get_test_setup_scripts() {
		return [
			[
				'asset-1',
				false,
			],
			[
				'asset-2',
				true,
			],
		];
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::add_setup_script
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::add_setup_script
	 *
	 * @dataProvider get_test_setup_scripts
	 *
	 * @param string  $handle   Script handle
	 * @param boolean $enqueued Whether to enqueue the script.
	 */
	public function test_add_setup_script( $handle, $enqueued ) {
		$filter_asset = static function( $asset, $asset_handle ) use ( $handle ) {
			if ( $handle !== $asset_handle ) {
				return $asset;
			}

			return [
				'dependencies' => [],
				'version'      => '1.0',
			];
		};

		add_filter( 'amp_setup_asset', $filter_asset, 10, 2 );
		$this->page->add_setup_script( $handle, $enqueued );
		remove_filter( 'amp_setup_asset', $filter_asset );

		$this->assertTrue( wp_script_is( $handle, $enqueued ? 'enqueued' : 'registered' ) );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::get_asset
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::get_asset
	 */
	public function test_get_asset() {
		$test_data = [
			'dependencies' => [],
			'version'      => '1.0',
		];

		$filter_asset = static function() use ( $test_data ) {
			return $test_data;
		};

		add_filter( 'amp_setup_asset', $filter_asset, 10, 2 );
		$asset = $this->page->get_asset( 'my-handle' );
		remove_filter( 'amp_setup_asset', $filter_asset );

		$this->assertEquals( $asset, $test_data );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::enqueue_assets
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::enqueue_assets
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-setup';

		$this->page->enqueue_assets( 'some-screen' );
		$this->assertFalse( wp_script_is( $handle ) );

		$this->page->enqueue_assets( $this->page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::should_override_scripts
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::should_override_scripts
	 */
	public function test_should_override_scripts() {
		global $wp_version;

		$original_wp_version = $wp_version;

		$wp_version = '4.9';
		$this->assertFalse( $this->page->should_override_scripts() );

		$wp_version = '5.0';
		$this->assertTrue( $this->page->should_override_scripts() );

		$wp_version = '5.4';
		$this->assertFalse( $this->page->should_override_scripts() );

		$wp_version = $original_wp_version;
	}

	/**
	 * Provides arguments for the test_override_scripts method.
	 *
	 * @return array Array of handles and should_override arguments.
	 */
	public function get_test_handles() {
		return [
			[ 'wp-element', true ],
			[ 'react', true ],
			[ 'wp-polyfill', true ],
			[ 'wp-element', false ],
			[ 'react', false ],
			[ 'wp-polyfill', false ],
		];
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu_Page::override_scripts
	 *
	 * @covers AMP_Setup_Wizard_Submenu_Page::override_scripts
	 *
	 * @dataProvider get_test_handles
	 */
	public function test_override_scripts( $handle, $should_override ) {
		global $wp_scripts, $wp_version;

		if ( version_compare( $wp_version, '5.0', '<' ) && 'wp-polyfill' === $handle ) {
			$should_override = false;
		}

		$wp_scripts = $this->old_wp_scripts;

		$filter_callback = $should_override ? '__return_true' : '__return_false';
		add_filter( 'amp_should_override_setup_scripts', $filter_callback );

		$this->page->override_scripts( $this->page->screen_handle() );

		$check = array_key_exists( $handle, $wp_scripts->registered ) && false !== strpos( $wp_scripts->registered[ $handle ]->src, 'plugins/amp' );

		if ( $should_override ) {
			$this->assertTrue( $check );
		} else {
			$this->assertFalse( $check );
		}

		remove_filter( 'amp_should_override_setup_scripts', $filter_callback );

		$wp_scripts = $this->old_wp_scripts;
	}
}
