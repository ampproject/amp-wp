<?php
/**
 * Test cases for AMP_Plugins class.
 *
 * @package AMP
 * @since   2.2.2
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Plugins.
 *
 * @package AMP
 * @coversDefaultClass AMP_Plugins
 */
class Test_AMP_Plugins extends TestCase {

	/**
	 * @var AMP_Plugins
	 */
	protected $instance;

	/**
	 * Setup method.
	 */
	public function setUp() {

		parent::setUp();

		$this->instance = new AMP_Plugins();
	}

	/**
	 * @covers ::init()
	 */
	public function test_init() {

		$this->instance->init();
		$this->assertFalse( has_filter( 'plugin_row_meta', [ $this->instance, 'filter_plugin_row_meta' ] ) );

		set_current_screen( 'index.php' );
		$this->instance->init();
		$this->assertEquals(
			10,
			has_filter( 'plugin_row_meta', [ $this->instance, 'filter_plugin_row_meta' ] )
		);

		set_current_screen( 'front' );
	}

	/**
	 * Data provider for $this->test_filter_plugin_row_meta()
	 *
	 * @return array[]
	 */
	public function data_provider_for_filter_plugin_row_meta() {

		return [
			'plugin is not suppressed'          => [
				'plugin_file'   => 'plugin.php',
				'plugin_data'   => [],
				'is_suppressed' => false,
			],
			'plugin is suppressed'              => [
				'plugin_file'   => 'plugin-one/plugin-one.php',
				'plugin_data'   => [
					'slug' => 'plugin-one',
				],
				'is_suppressed' => true,
			],
			'plugin is suppressed without slug' => [
				'plugin_file'   => 'plugin-one/plugin-one.php',
				'plugin_data'   => [],
				'is_suppressed' => true,
			],
		];

	}

	/**
	 * @dataProvider data_provider_for_filter_plugin_row_meta()
	 * @covers ::get_suppressed_plugins()
	 * @covers ::filter_plugin_row_meta()
	 */
	public function test_filter_plugin_row_meta( $plugin_file, $plugin_data, $is_suppressed ) {

		// Mock AMP option.
		AMP_Options_Manager::update_option(
			'suppressed_plugins',
			[
				'plugin-one' => [
					'last_version' => '1.0',
					'timestamp'    => 1646316249,
					'username'     => 'user1',
				],
			]
		);

		$output = $this->instance->filter_plugin_row_meta( [], $plugin_file, $plugin_data );

		if ( $is_suppressed ) {

			$this->assertEquals(
				sprintf(
					'<a href="%s" aria-label="%s" target="_blank">%s</a>',
					esc_url( admin_url( 'admin.php?page=amp-options' ) ),
					esc_attr__( 'Visit AMP Settings', 'amp' ),
					__( 'Suppressed on AMP Pages', 'amp' )
				),
				$output[0]
			);
		} else {
			$this->assertEmpty( $output );
		}
	}
}
