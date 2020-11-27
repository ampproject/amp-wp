<?php
/**
 * Tests for AmpRESTContext.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\AmpRESTContext;
use WP_REST_Request;

/**
 * Tests for AmpRESTContext.
 *
 * @group amp-options
 *
 * @coversDefaultClass \AmpProject\AmpWP\AmpRESTContext
 */
class AmpRESTContextTest extends DependencyInjectedTestCase {

	/**
	 * Test instance.
	 *
	 * @var AmpRESTContext
	 */
	private $instance;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new AmpRESTContext();
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', AmpRESTContext::get_registration_action() );
	}

	/** @covers ::register() */
	public function test_register() {
		global $wp_rest_additional_fields;

		$this->instance->register();

		register_post_type( 'my-post-type' );

		$this->assertEquals( 10, has_filter( 'rest_prepare_post', [ $this->instance, 'add_content_amp_field' ] ) );
		$this->assertEquals( 10, has_filter( 'rest_prepare_page', [ $this->instance, 'add_content_amp_field' ] ) );
		$this->assertFalse( has_filter( 'rest_prepare_my-post-type', [ $this->instance, 'add_content_amp_field' ] ) );

		$this->assertEquals( 10, has_filter( 'rest_post_item_schema', [ $this->instance, 'extend_content_schema' ] ) );
		$this->assertEquals( 10, has_filter( 'rest_post_item_schema', [ $this->instance, 'extend_content_schema' ] ) );
		$this->assertFalse( has_filter( 'rest_my-post-type_item_schema', [ $this->instance, 'extend_content_schema' ] ) );

		$this->assertArrayHasKey( AmpRESTContext::AMP_LINKS_REST_FIELD, $wp_rest_additional_fields['post'] );
		$this->assertArrayHasKey( AmpRESTContext::AMP_LINKS_REST_FIELD, $wp_rest_additional_fields['page'] );
	}

	/** @covers ::add_amp_context_where_context_has_view() */
	public function test_add_amp_context_where_context_has_view() {
		$this->assertEquals( 'invalid-schema', $this->instance->add_amp_context_where_context_has_view( 'invalid-schema' ) );

		// Test top-level context with no view.
		$this->assertEquals(
			[
				'context' => [ 'edit' ],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context' => [ 'edit' ],
				]
			)
		);

		// Test top-level context with view.
		$this->assertEquals(
			[
				'context' => [ 'edit', 'view', 'amp' ],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context' => [ 'edit', 'view' ],
				]
			)
		);

		// No recursion if type is missing.
		$this->assertEquals(
			[
				'context'    => [ 'edit', 'view', 'amp' ],
				'properties' => [
					'my-property' => [
						'context' => [ 'edit', 'view' ],
					],
				],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context'    => [ 'edit', 'view' ],
					'properties' => [
						'my-property' => [
							'context' => [ 'edit', 'view' ],
						],
					],
				]
			)
		);
		$this->assertEquals(
			[
				'context' => [ 'edit', 'view', 'amp' ],
				'items'   => [
					[
						'context' => [ 'edit', 'view' ],
					],
				],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context' => [ 'edit', 'view' ],
					'items'   => [
						[
							'context' => [ 'edit', 'view' ],
						],
					],
				]
			)
		);

		// Recurse if type is present.
		$this->assertEquals(
			[
				'context'    => [ 'edit', 'view', 'amp' ],
				'type'       => 'object',
				'properties' => [
					'my-property' => [
						'type'    => 'string',
						'context' => [ 'edit', 'view', 'amp' ],
					],
				],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context'    => [ 'edit', 'view' ],
					'type'       => 'object',
					'properties' => [
						'my-property' => [
							'type'    => 'string',
							'context' => [ 'edit', 'view' ],
						],
					],
				]
			)
		);
		$this->assertEquals(
			[
				'context' => [ 'edit', 'view', 'amp' ],
				'type'    => 'array',
				'items'   => [
					[
						'type'    => 'string',
						'context' => [ 'edit', 'view', 'amp' ],
					],
				],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context' => [ 'edit', 'view' ],
					'type'    => 'array',
					'items'   => [
						[
							'type'    => 'string',
							'context' => [ 'edit', 'view' ],
						],
					],
				]
			)
		);

		// Test deep mixed types.
		$this->assertEquals(
			[
				'context'    => [ 'edit', 'view', 'amp' ],
				'type'       => 'object',
				'properties' => [
					'blue'               => [
						'type'    => 'string',
						'context' => [ 'view', 'amp' ],
					],
					'red'                => [
						'type'    => 'string',
						'context' => [ 'edit' ],
					],
					'my-array-property'  => [
						'type'    => 'array',
						'context' => [ 'edit', 'view', 'amp' ],
						'items'   => [
							[
								'context' => [ 'edit' ],
							],
							[
								'context'    => [ 'view', 'edit', 'amp' ],
								'type'       => 'object',
								'properties' => [
									'orange' => [
										'type'    => 'string',
										'context' => [ 'edit' ],
									],
									'green'  => [
										'type'    => 'string',
										'context' => [ 'edit', 'view', 'amp' ],
									],
								],
							],
						],
					],
					'my-object-property' => [
						'type'       => 'object',
						'context'    => [ 'view', 'embed', 'amp' ],
						'properties' => [
							'yellow' => [
								'context' => [ 'edit' ],
							],
							'purple' => [
								'context'    => [ 'view', 'embed', 'amp' ],
								'type'       => 'object',
								'properties' => [
									'purple-subproperty' => [
										'context' => [ 'view', 'amp' ],
										'type'    => 'array',
										'items'   => [
											[
												'type'    => 'string',
												'context' => [ 'view', 'edit', 'embed', 'amp' ],
											],
										],
									],
								],
							],
						],
					],
				],
			],
			$this->instance->add_amp_context_where_context_has_view(
				[
					'context'    => [ 'edit', 'view' ],
					'type'       => 'object',
					'properties' => [
						'blue'               => [
							'type'    => 'string',
							'context' => [ 'view' ],
						],
						'red'                => [
							'type'    => 'string',
							'context' => [ 'edit' ],
						],
						'my-array-property'  => [
							'type'    => 'array',
							'context' => [ 'edit', 'view' ],
							'items'   => [
								[
									'context' => [ 'edit' ],
								],
								[
									'context'    => [ 'view', 'edit' ],
									'type'       => 'object',
									'properties' => [
										'orange' => [
											'type'    => 'string',
											'context' => [ 'edit' ],
										],
										'green'  => [
											'type'    => 'string',
											'context' => [ 'edit', 'view' ],
										],
									],
								],
							],
						],
						'my-object-property' => [
							'type'       => 'object',
							'context'    => [ 'view', 'embed' ],
							'properties' => [
								'yellow' => [
									'context' => [ 'edit' ],
								],
								'purple' => [
									'context'    => [ 'view', 'embed' ],
									'type'       => 'object',
									'properties' => [
										'purple-subproperty' => [
											'context' => [ 'view' ],
											'type'    => 'array',
											'items'   => [
												[
													'type' => 'string',
													'context' => [ 'view', 'edit', 'embed' ],
												],
											],
										],
									],
								],
							],
						],
					],
				]
			)
		);
	}

	/** @covers ::extend_content_schema */
	public function test_extend_content_schema() {
		$this->assertEquals( 'invalid-schema', $this->instance->extend_content_schema( 'invalid-schema' ) );
		$this->assertEquals( [ 'invalid' => 'schema' ], $this->instance->extend_content_schema( [ 'invalid' => 'schema' ] ) );

		$actual = $this->instance->extend_content_schema(
			[
				'properties' => [
					'content' => [
						'properties' => [],
					],
				],
			]
		);

		$this->assertArrayHasKey( 'amp', $actual['properties']['content']['properties'] );
	}

	/** @covers ::get_amp_links() */
	public function test_get_amp_links() {
		$id = $this->factory()->post->create();

		$actual = $this->instance->get_amp_links( compact( 'id' ) );

		$this->assertArrayHasKey( 'standalone_content', $actual );
		$this->assertArrayHasKey( 'complete_template', $actual );
		$this->assertArrayHasKey( 'origin', $actual['standalone_content'] );
		$this->assertArrayHasKey( 'cache', $actual['standalone_content'] );
		$this->assertArrayHasKey( 'origin', $actual['complete_template'] );
		$this->assertArrayHasKey( 'cache', $actual['complete_template'] );
	}

	/** @covers ::add_content_amp_field() */
	public function test_responses_without_amp_context_are_unaffected() {
		$post_id = $this->factory()->post->create();

		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$data = rest_do_request( $request )->get_data();

		$this->assertArrayNotHasKey( AmpRESTContext::AMP_LINKS_REST_FIELD, $data );
		$this->assertArrayNotHasKey( 'amp', $data['content'] );
	}

	/** @covers ::add_content_amp_field() */
	public function test_add_content_amp_field_with_supported_post_type() {
		$this->instance->register();
		$post_id = $this->factory()->post->create(
			[
				'post_content' => implode(
					',',
					[
						'<figure data-amp-lightbox="true"><img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg, https://example.com/image-1.jpg     512w, https://example.com/image-2.jpg 1024w   , https://example.com/image-3.jpg 300w, https://example.com/image-4.jpg 768w" width="350" height="150"></figure>',
						'<video width="300" height="300" src="https://example.com/video.mp4"></video>',
					]
				),
			]
		);

		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$request->set_param( 'context', 'amp' );
		$data = rest_do_request( $request )->get_data();

		$this->assertArrayHasKey( AmpRESTContext::AMP_LINKS_REST_FIELD, $data );
		$this->assertArrayHasKey( 'amp', $data['content'] );
		$this->assertArrayHasKey( 'markup', $data['content']['amp'] );
		$this->assertArrayHasKey( 'styles', $data['content']['amp'] );
		$this->assertArrayHasKey( 'scripts', $data['content']['amp'] );
		$this->assertArrayHasKey( 'amp-lightbox-gallery', $data['content']['amp']['scripts'] );
		$this->assertArrayHasKey( 'amp-video', $data['content']['amp']['scripts'] );
		$this->assertContains( '<amp-img', $data['content']['amp']['markup'] );
		$this->assertContains( '<amp-img', $data['content']['amp']['markup'] );
	}
}
