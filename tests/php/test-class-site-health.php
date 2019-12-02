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
		remove_post_type_support( 'post', AMP_Theme_Support::SLUG );
	}

	/**
	 * Test init.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'site_status_tests', [ $this->instance, 'add_tests' ] ) );
		$this->assertEquals( 10, has_action( 'debug_information', [ $this->instance, 'add_debug_information' ] ) );
		$this->assertEquals( 10, has_action( 'site_status_test_php_modules', [ $this->instance, 'add_extension' ] ) );
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

	/**
	 * Test add_debug_information.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::add_debug_information()
	 */
	public function test_add_debug_information() {
		$this->assertArraySubset(
			[
				'amp' => [
					'label'       => 'AMP',
					'description' => 'Debugging information for the Official AMP Plugin for WordPress.',
					'fields'      => [
						'amp_mode_enabled'        => [
							'label'   => 'AMP mode enabled',
							'private' => false,
						],
						'amp_experiences_enabled' => [
							'label'   => 'AMP experiences enabled',
							'private' => false,
						],
						'amp_templates_enabled'   => [
							'label'   => 'Templates enabled',
							'private' => false,
						],
					],
				],
			],
			$this->instance->add_debug_information( [] )
		);
	}

	/**
	 * Gets the test data for test_get_experiences_enabled().
	 *
	 * @return array The test data.
	 */
	public function get_experiences_enabled_data() {
		return [
			'no_experience_enabled' => [
				[],
				'No experience enabled',
			],
			'only_website'          => [
				[ AMP_Options_Manager::WEBSITE_EXPERIENCE ],
				'website',
			],
			'only_stories'          => [
				[ AMP_Options_Manager::STORIES_EXPERIENCE ],
				'stories',
			],
			'website_and_stories'   => [
				[ AMP_Options_Manager::WEBSITE_EXPERIENCE, AMP_Options_Manager::STORIES_EXPERIENCE ],
				'website, stories',
			],
		];
	}

	/**
	 * Test add_debug_information.
	 *
	 * @dataProvider get_experiences_enabled_data
	 * @covers \Amp\AmpWP\Admin\SiteHealth::add_debug_information()
	 *
	 * @param array  $experiences_enabled The AMP experiences that are enabled, if any.
	 * @param string $expected            The expected return value.
	 */
	public function test_get_experiences_enabled( $experiences_enabled, $expected ) {
		AMP_Options_Manager::update_option( 'experiences', $experiences_enabled );
		$this->assertEquals( $expected, $this->instance->get_experiences_enabled() );
	}

	/**
	 * Gets the test data for test_get_supported_templates().
	 *
	 * @return array The test data.
	 */
	public function get_supported_templates_data() {
		return [
			'no_template_supported'       => [
				[],
				[],
				'standard',
				'No template supported',
			],
			'only_singular'               => [
				[],
				[ 'is_singular' ],
				'transitional',
				'is_singular',
			],
			'only_post'                   => [
				[ 'post' ],
				[],
				'transitional',
				'post',
			],
			'only_post_and_author'        => [
				[ 'post' ],
				[ 'is_author' ],
				'transitional',
				'post, is_author',
			],
			'two_templates'               => [
				[ 'post' ],
				[ 'is_singular', 'is_author' ],
				'transitional',
				'post, is_singular, is_author',
			],
			'three_templates'             => [
				[ 'post', 'page' ],
				[ 'is_singular', 'is_author', 'is_tag' ],
				'transitional',
				'post, page, is_singular, is_author, is_tag',
			],
			'three_templates_reader_mode' => [
				[ 'post', 'page' ],
				[ 'is_singular', 'is_author', 'is_tag' ],
				'reader',
				'post, page',
			],
		];
	}

	/**
	 * Test add_debug_information.
	 *
	 * @dataProvider get_supported_templates_data
	 * @covers \Amp\AmpWP\Admin\SiteHealth::get_supported_templates()
	 *
	 * @param array  $supported_content_types The supported content types, like 'post'.
	 * @param array  $supported_templates     The supported templates, like 'is_author'.
	 * @param string $theme_support           The theme support, like 'standard'.
	 * @param string $expected                The expected string of supported templates.
	 */
	public function test_get_supported_templates( $supported_content_types, $supported_templates, $theme_support, $expected ) {
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		AMP_Options_Manager::update_option( 'supported_templates', $supported_templates );
		AMP_Options_Manager::update_option( 'theme_support', $theme_support );
		AMP_Theme_Support::read_theme_support();

		foreach ( $supported_content_types as $post_type ) {
			add_post_type_support( $post_type, AMP_Theme_Support::SLUG );
		}

		$this->assertEquals( $expected, $this->instance->get_supported_templates() );
	}

	/**
	 * Test add_extension.
	 *
	 * @covers \Amp\AmpWP\Admin\SiteHealth::add_extension()
	 */
	public function test_add_extension() {
		$this->assertEquals(
			[
				'spl' => [
					'extension' => 'spl',
					'function'  => 'spl_autoload_register',
					'required'  => true,
				],
			],
			$this->instance->add_extension( [] )
		);
	}
}
