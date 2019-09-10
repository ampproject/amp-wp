<?php
/**
 * Test AMP_Story_Media.
 *
 * @package AMP
 */

/**
 * Test AMP_Story_Media.
 */
class AMP_Story_Media_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		if ( ! AMP_Story_Post_Type::has_required_block_capabilities() ) {
			$this->markTestSkipped( 'The function register_block_type() is not present, so the AMP Story post type was not registered.' );
		}

		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $block ) {
				if ( 'amp/' === substr( $block->name, 0, 4 ) ) {
					WP_Block_Type_Registry::get_instance()->unregister( $block->name );
				}
			}
		}

		global $wp_styles;
		$wp_styles = null;
		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::STORIES_EXPERIENCE ] );

		AMP_Story_Post_Type::register(); // This calls AMP_Story_Media::init().
	}

	/**
	 * Reset the permalink structure to the state before the tests.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function tearDown() {
		global $wp_rewrite;

		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::WEBSITE_EXPERIENCE ] );
		unregister_post_type( AMP_Story_Post_Type::POST_TYPE_SLUG );

		$wp_rewrite->set_permalink_structure( false );
		unset( $_SERVER['HTTPS'] );
		unset( $GLOBALS['current_screen'] );
		parent::tearDown();
	}

	/**
	 * Test filter_schemaorg_metadata_images()
	 *
	 * @covers AMP_Story_Media::filter_schemaorg_metadata_images
	 */
	public function test_filter_schemaorg_metadata_images() {
		$this->assertSame( [], AMP_Story_Media::filter_schemaorg_metadata_images( [] ) );

		$post_id = $this->factory()->post->create( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_singular( AMP_Story_Post_Type::POST_TYPE_SLUG ) );

		$custom_image_url   = 'https://example.com/foo.jpg';
		$custom_image_obj   = [
			'url'   => $custom_image_url,
			'@type' => 'ImageObject',
		];
		$fallback_image_url = amp_get_asset_url( 'images/stories-editor/story-fallback-poster.jpg' );

		$this->assertSame( [ 'image' => [ $fallback_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [] ) );
		$this->assertSame( [ 'image' => [ $fallback_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [ 'image' => null ] ) );
		$this->assertSame( [ 'image' => [ $fallback_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [ 'image' => false ] ) );
		$this->assertSame( [ 'image' => [ $fallback_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [ 'image' => '' ] ) );

		$this->assertSame( [ 'image' => [ $fallback_image_url, $custom_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [ 'image' => $custom_image_url ] ) );
		$this->assertSame(
			[
				'image' => [
					$fallback_image_url,
					$custom_image_obj,
				],
			],
			AMP_Story_Media::filter_schemaorg_metadata_images(
				[
					'image' => $custom_image_obj,
				]
			)
		);
		$this->assertSame( [ 'image' => [ $fallback_image_url, $custom_image_url ] ], AMP_Story_Media::filter_schemaorg_metadata_images( [ 'image' => $custom_image_url ] ) );
		$this->assertSame(
			[
				'image' => [
					$fallback_image_url,
					$custom_image_obj,
					$custom_image_obj,
				],
			],
			AMP_Story_Media::filter_schemaorg_metadata_images(
				[
					'image' => [ $custom_image_obj, $custom_image_obj ],
				]
			)
		);
	}

	/**
	 * Test rest_api_init()
	 *
	 * @covers AMP_Story_Media::rest_api_init
	 */
	public function test_rest_api_init() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$poster_attachment_id = self::factory()->attachment->create_object(
			[
				'file'           => DIR_TESTDATA . '/images/test-image.jpg',
				'post_parent'    => 0,
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'Test Image',
			]
		);
		$video_attachment_id  = self::factory()->attachment->create_object(
			[
				'file'           => DIR_TESTDATA . '/images/test-image.jpg',
				'post_parent'    => 0,
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'Test Image',
			]
		);

		set_post_thumbnail( $video_attachment_id, $poster_attachment_id );

		$request  = new WP_REST_Request( 'GET', sprintf( '/wp/v2/media/%d', $video_attachment_id ) );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( $poster_attachment_id, $data['featured_media'] );
	}
}
