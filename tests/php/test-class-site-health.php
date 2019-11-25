<?php
/**
 * Test Site_Health.
 *
 * @package AMP
 */

use Amp\AmpWP\Admin\SiteHealth;

/**
 * Test Site_Health.
 */
class Test_Site_Health extends WP_UnitTestCase {

	/**
	 * The tested instance.
	 *
	 * @var SiteHealth
	 */
	public $instance;

	/**
	 * Sets up each test.
	 *
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new SiteHealth();
	}

	/**
	 * Test init.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'site_status_tests', [ $this->instance, 'add_tests' ] ) );
	}

	/**
	 * Test add_tests.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::add_tests()
	 */
	public function test_add_tests() {
		$this->assertEquals(
			[
				'direct' => [
					'persistent_object_cache' => [
						'label' => 'Persistent object cache',
						'test'  => [ $this->instance, 'persistent_object_cache' ],
					],
					'curl_multi_functions'    => [
						'label' => 'curl_multi_* functions',
						'test'  => [ $this->instance, 'curl_multi_functions' ],
					],
					'amp_mode_enabled'        => [
						'label' => 'AMP mode enabled',
						'test'  => [ $this->instance, 'amp_mode_enabled' ],
					],
					'amp_experiences_enabled' => [
						'label' => 'AMP experiences enabled',
						'test'  => [ $this->instance, 'amp_experiences_enabled' ],
					],
					'amp_templates_enabled'   => [
						'label' => 'AMP templates enabled',
						'test'  => [ $this->instance, 'amp_templates_enabled' ],
					],
				],
			],
			$this->instance->add_tests( [] )
		);
	}

	/**
	 * Test persistent_object_cache.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::persistent_object_cache()
	 */
	public function test_persistent_object_cache() {
		$GLOBALS['_wp_using_ext_object_cache'] = false;
		$this->assertEquals(
			[
				'label'       => 'Persistent object caching is not enabled',
				'status'      => 'recommended',
				'badge'       => [
					'label' => 'AMP',
					'color' => 'orange',
				],
				'description' => 'The AMP plugin performs at its best when persistent object cache is enabled.',
				'actions'     => '<p><a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching" target="_blank" rel="noopener noreferrer">Learn more about persistent object caching <span class="screen-reader-text">(opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				'test'        => 'persistent_object_cache',
			],
			$this->instance->persistent_object_cache()
		);

		$GLOBALS['_wp_using_ext_object_cache'] = true;
		$this->assertEquals(
			[
				'label'       => 'Persistent object caching is enabled',
				'status'      => 'good',
				'badge'       => [
					'label' => 'AMP',
					'color' => 'green',
				],
				'description' => 'The AMP plugin performs at its best when persistent object cache is enabled.',
				'actions'     => '',
				'test'        => 'persistent_object_cache',
			],
			$this->instance->persistent_object_cache()
		);
	}

	/**
	 * Test curl_multi_functions.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::curl_multi_functions()
	 */
	public function test_curl_multi_functions() {
		$this->assertArraySubset(
			[
				'description' => 'The AMP plugin performs better when these functions are available.',
				'actions'     => '<p><a href="https://www.php.net/manual/book.curl.php" target="_blank" rel="noopener noreferrer">Learn more about these functions <span class="screen-reader-text">(opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				'test'        => 'curl_multi_functions',
			],
			$this->instance->curl_multi_functions()
		);
	}
}
