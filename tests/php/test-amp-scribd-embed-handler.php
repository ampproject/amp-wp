<?php
/**
 * Class AMP_Scribd_Embed_Handler_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Scribd_Embed_Handler_Test
 *
 * @covers AMP_Scribd_Embed_Handler
 */
class AMP_Scribd_Embed_Handler_Test extends WP_UnitTestCase {

	/**
	 * Scribd document URL.
	 *
	 * @var string
	 */
	protected $scribd_doc_url = 'https://www.scribd.com/document/110799637/Synthesis-of-Knowledge-Effects-of-Fire-and-Thinning-Treatments-on-Understory-Vegetation-in-Dry-U-S-Forests';

	/**
	 * Set up.
	 *
	 * @global WP_Post $post
	 */
	public function setUp() {
		global $post;
		parent::setUp();

		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );

		/*
		 * As #34115 in 4.9 a post is not needed for context to run oEmbeds. Prior ot 4.9, the WP_Embed::shortcode()
		 * method would short-circuit when this is the case:
		 * https://github.com/WordPress/wordpress-develop/blob/4.8.4/src/wp-includes/class-wp-embed.php#L192-L193
		 * So on WP<4.9 we set a post global to ensure oEmbeds get processed.
		 */
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {
			$post = self::factory()->post->create_and_get();
		}
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ] );
		parent::tearDown();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $preempt, $r, $url ) {
		if ( false === strpos( $url, 'scribd.com' ) ) {
			return $preempt;
		}

		return [
			'body'     => '{"type":"rich","version":"1.0","provider_name":"Scribd","provider_url":"https://www.scribd.com/","cache_age":604800,"title":"Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests","author_name":"Joint Fire Science Program","author_url":"https://www.scribd.com/user/151878975/Joint-Fire-Science-Program","thumbnail_url":"https://imgv2-1-f.scribdassets.com/img/document/110799637/111x142/9fc8621525/1570598026?v=1","thumbnail_width":164,"thumbnail_height":212,"html":"\u003ciframe class=\"scribd_iframe_embed\" src=\"https://www.scribd.com/embeds/110799637/content\" data-aspect-ratio=\"1.2941176470588236\" scrolling=\"no\" id=\"110799637\" width=\"100%\" height=\"500\" frameborder=\"0\"\u003e\u003c/iframe\u003e\u003cscript type=\"text/javascript\"\u003e\n          (function() { var scribd = document.createElement(\"script\"); scribd.type = \"text/javascript\"; scribd.async = true; scribd.src = \"https://www.scribd.com/javascripts/embed_code/inject.js\"; var s = document.getElementsByTagName(\"script\")[0]; s.parentNode.insertBefore(scribd, s); })()\n        \u003c/script\u003e"}',
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
		];
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$data = [
			'no_embed'       => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'document_embed' => [
				$this->scribd_doc_url . PHP_EOL,
				'<p><iframe sandbox="allow-scripts allow-popups" title="Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests" class="scribd_iframe_embed" src="https://www.scribd.com/embeds/110799637/content" data-aspect-ratio="1.2941176470588236" scrolling="no" id="110799637" width="100%" height="500" frameborder="0"></iframe></p>' . PHP_EOL,
			],
		];

		return $data;
	}

	/**
	 * Test conversion.
	 *
	 * @covers AMP_Scribd_Embed_Handler::filter_embed_oembed_html()
	 * @covers AMP_Scribd_Embed_Handler::remove_script()
	 * @covers AMP_Scribd_Embed_Handler::inject_sandbox_attribute()
	 * @dataProvider get_conversion_data
	 *
	 * @param $source
	 * @param $expected
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Scribd_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array Scripts data.
	 */
	public function get_scripts_data() {
		return [
			'not_converted'      => [
				'<p>Hello World.</p>',
				[],
			],
			'converted_document' => [
				$this->scribd_doc_url . PHP_EOL,
				[],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @covers AMP_Scribd_Embed_Handler::get_scripts()
	 * @dataProvider get_scripts_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Scribd_Embed_Handler();
		$embed->register_embed();
		$source = apply_filters( 'the_content', $source );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

}
