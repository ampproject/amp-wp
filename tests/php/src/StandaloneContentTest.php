<?php
/**
 * Tests for StandaloneContent.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\StandaloneContent;

/**
 * Tests for StandaloneContent.
 *
 * @coversDefaultClass \AmpProject\AmpWP\StandaloneContent
 */
class StandaloneContentTest extends DependencyInjectedTestCase {

	/**
	 * Test instance.
	 *
	 * @var StandaloneContent
	 */
	private $instance;

	/**
	 * Saved $_GET array to restore after tests.
	 *
	 * @var array
	 */
	private $saved_get;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new StandaloneContent();

		$this->saved_get = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();

		$_GET = $this->saved_get;
	}

	/**
	 * Navigates to the standalone template for a post.
	 */
	private function set_up_standalone_post() {
		$post_id = $this->factory()->post->create();

		$this->go_to( add_query_arg( StandaloneContent::STANDALONE_CONTENT_QUERY_VAR, '', get_permalink( $post_id ) ) );

		$this->instance->register();
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'wp', StandaloneContent::get_registration_action() );
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		$this->assertFalse( StandaloneContent::is_needed() );

		$_GET[ StandaloneContent::STANDALONE_CONTENT_QUERY_VAR ] = '';

		$this->assertTrue( StandaloneContent::is_needed() );
	}

	/** @covers ::is_standalone_content_request() */
	public function test_is_standalone_content_request() {
		$this->assertFalse( StandaloneContent::is_standalone_content_request() );

		$_GET[ StandaloneContent::STANDALONE_CONTENT_QUERY_VAR ] = '';

		$this->assertTrue( StandaloneContent::is_standalone_content_request() );
	}

	/** @covers ::register() */
	public function test_register_error() {
		try {
			$this->instance->register();
		} catch ( \WPDieException $e ) {
			$this->assertEquals( 'AMP content is not available for this URL.', $e->getMessage() );
		}
	}

	/** @covers ::register() */
	public function test_register() {
		$this->set_up_standalone_post();
		$this->assertEquals( 10, has_filter( 'show_admin_bar', '__return_false' ) );
		$this->assertEquals( 10, has_filter( 'print_styles_array', [ $this->instance, 'remove_unneeded_styles' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'template_include', [ $this->instance, 'include_standalone_content_template' ] ) );
	}

	/** @covers ::remove_unneeded_styles() */
	public function test_remove_unneeded_styles() {
		$this->assertEquals(
			[
				'amp-default',
				'wp-block-library',
				'wp-block-library-theme',
			],
			$this->instance->remove_unneeded_styles(
				[
					'style-1',
					'style-2',
					'amp-default',
					'wp-block-library',
					'style-3',
					'wp-block-library-theme',
					'style-4',
				]
			)
		);
	}

	/** @covers ::include_standalone_content_template() */
	public function test_include_standalone_content_template() {
		$this->assertEquals(
			AMP__DIR__ . '/includes/templates/standalone-content.php',
			$this->instance->include_standalone_content_template()
		);
	}
}
