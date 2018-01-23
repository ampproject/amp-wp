<?php
/**
 * Test AMP helper functions.
 *
 * @package AMP
 */

/**
 * Class Test_AMP_Helper_Functions
 */
class Test_AMP_Helper_Functions extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_theme_support( 'amp' );
		parent::tearDown();
	}

	/**
	 * Filter for amp_pre_get_permalink and amp_get_permalink.
	 *
	 * @param string $url     URL.
	 * @param int    $post_id Post ID.
	 * @return string URL.
	 */
	public function return_example_url( $url, $post_id ) {
		$current_filter = current_filter();
		return 'http://overridden.example.com/?' . build_query( compact( 'url', 'post_id', 'current_filter' ) );
	}

	/**
	 * Test amp_get_permalink() without pretty permalinks.
	 *
	 * @covers \amp_get_permalink()
	 */
	public function test_amp_get_permalink_without_pretty_permalinks() {
		delete_option( 'permalink_structure' );
		flush_rewrite_rules();

		$drafted_post   = $this->factory()->post->create( array(
			'post_name'   => 'draft',
			'post_status' => 'draft',
			'post_type'   => 'post',
		) );
		$published_post = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'post',
		) );
		$published_page = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );

		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_page ) );

		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_pre_get_permalink', $url );
		$this->assertContains( 'url=0', $url );

		remove_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_get_permalink', $url );
	}

	/**
	 * Test amp_get_permalink() with pretty permalinks.
	 *
	 * @covers \amp_get_permalink()
	 */
	public function test_amp_get_permalink_with_pretty_permalinks() {
		global $wp_rewrite;
		update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
		$wp_rewrite->use_trailing_slashes = true;
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();

		$drafted_post   = $this->factory()->post->create( array(
			'post_name'   => 'draft',
			'post_status' => 'draft',
		) );
		$published_post = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
		) );
		$published_page = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );

		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '/amp/', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '?amp', amp_get_permalink( $published_page ) );

		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_pre_get_permalink', $url );
		$this->assertContains( 'url=0', $url );

		remove_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_get_permalink', $url );
	}

	/**
	 * Filter calls.
	 *
	 * @var array
	 */
	protected $last_filter_call;

	/**
	 * Capture filter call.
	 *
	 * @param mixed $value Value.
	 * @return mixed Value.
	 */
	public function capture_filter_call( $value ) {
		$this->last_filter_call = array(
			'current_filter' => current_filter(),
			'args'           => func_get_args(),
		);
		return $value;
	}

	/**
	 * Test amp_get_content_embed_handlers().
	 *
	 * @covers amp_get_content_embed_handlers()
	 */
	public function test_amp_get_content_embed_handlers() {
		$post = $this->factory()->post->create_and_get();
		add_filter( 'amp_content_embed_handlers', array( $this, 'capture_filter_call' ), 10, 2 );

		$this->last_filter_call = null;
		add_theme_support( 'amp' );
		$handlers = amp_get_content_embed_handlers();
		$this->assertArrayHasKey( 'AMP_SoundCloud_Embed_Handler', $handlers );
		$this->assertEquals( 'amp_content_embed_handlers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertNull( $this->last_filter_call['args'][1] );

		$this->last_filter_call = null;
		remove_theme_support( 'amp' );
		$handlers = amp_get_content_embed_handlers( $post );
		$this->assertArrayHasKey( 'AMP_SoundCloud_Embed_Handler', $handlers );
		$this->assertEquals( 'amp_content_embed_handlers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertEquals( $post, $this->last_filter_call['args'][1] );
	}

	/**
	 * Test deprecated $post param for amp_get_content_embed_handlers().
	 *
	 * @covers amp_get_content_embed_handlers()
	 */
	public function test_amp_get_content_embed_handlers_deprecated_param() {
		$post = $this->factory()->post->create_and_get();
		$this->setExpectedDeprecated( 'amp_get_content_embed_handlers' );
		add_theme_support( 'amp' );
		amp_get_content_embed_handlers( $post );
	}

	/**
	 * Test amp_get_content_sanitizers().
	 *
	 * @covers amp_get_content_sanitizers()
	 */
	public function test_amp_get_content_sanitizers() {
		$post = $this->factory()->post->create_and_get();
		add_filter( 'amp_content_sanitizers', array( $this, 'capture_filter_call' ), 10, 2 );

		$this->last_filter_call = null;
		add_theme_support( 'amp' );
		$handlers = amp_get_content_sanitizers();
		$this->assertArrayHasKey( 'AMP_Style_Sanitizer', $handlers );
		$this->assertEquals( 'amp_content_sanitizers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$handler_classes = array_keys( $handlers );
		$this->assertNull( $this->last_filter_call['args'][1] );
		$this->assertEquals( 'AMP_Tag_And_Attribute_Sanitizer', end( $handler_classes ) );

		$this->last_filter_call = null;
		remove_theme_support( 'amp' );
		$handlers = amp_get_content_sanitizers( $post );
		$this->assertArrayHasKey( 'AMP_Style_Sanitizer', $handlers );
		$this->assertEquals( 'amp_content_sanitizers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertEquals( $post, $this->last_filter_call['args'][1] );
	}

	/**
	 * Test deprecated $post param for amp_get_content_sanitizers().
	 *
	 * @covers amp_get_content_sanitizers()
	 */
	public function test_amp_get_content_sanitizers_deprecated_param() {
		$post = $this->factory()->post->create_and_get();
		$this->setExpectedDeprecated( 'amp_get_content_sanitizers' );
		add_theme_support( 'amp' );
		amp_get_content_sanitizers( $post );
	}

	/**
	 * Test post_supports_amp().
	 *
	 * @covers \post_supports_amp()
	 */
	public function test_post_supports_amp() {
		add_post_type_support( 'page', AMP_QUERY_VAR );

		// Test disabled by default for page for posts and show on front.
		update_option( 'show_on_front', 'page' );
		$post = $this->factory()->post->create_and_get( array( 'post_type' => 'page' ) );
		$this->assertTrue( post_supports_amp( $post ) );
		update_option( 'show_on_front', 'page' );
		$this->assertTrue( post_supports_amp( $post ) );
		update_option( 'page_for_posts', $post->ID );
		$this->assertFalse( post_supports_amp( $post ) );
		update_option( 'page_for_posts', '' );
		update_option( 'page_on_front', $post->ID );
		$this->assertFalse( post_supports_amp( $post ) );
		update_option( 'show_on_front', 'posts' );
		$this->assertTrue( post_supports_amp( $post ) );

		// Test disabled by default for page templates.
		update_post_meta( $post->ID, '_wp_page_template', 'foo.php' );
		$this->assertFalse( post_supports_amp( $post ) );

		// Reset.
		remove_post_type_support( 'page', AMP_QUERY_VAR );
	}
}
