<?php

namespace AmpProject\AmpWP\Tests\Optimizer;

use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Optimizer\HeroCandidateFiltering;
use AmpProject\Attribute;
use AMP_Options_Manager;
use AmpProject\AmpWP\Option;
use AMP_Theme_Support;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\HeroCandidateFiltering */
final class HeroCandidateFilteringTest extends DependencyInjectedTestCase {

	/** @var string[] */
	const ARRAY_WITH_DATA_HERO_CANDIDATE_ATTR = [ Attribute::DATA_HERO_CANDIDATE => '' ];

	/** @var HeroCandidateFiltering */
	private $instance;

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();
		$this->instance = $this->injector->make( HeroCandidateFiltering::class );
	}

	/** @covers ::get_registration_action()  */
	public function test_get_registration_action() {
		$this->assertSame( 'wp', HeroCandidateFiltering::get_registration_action() );
	}

	/** @covers ::is_needed()  */
	public function test_is_needed() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );
		$this->assertFalse( amp_is_request() );
		$this->assertFalse( HeroCandidateFiltering::is_needed() );

		$this->go_to( amp_get_permalink( $post_id ) );
		$this->assertTrue( amp_is_request() );
		$this->assertTrue( HeroCandidateFiltering::is_needed() );
	}

	/** @covers ::register()  */
	public function test_register() {
		$this->instance->register();
		$this->assertSame( 10, has_filter( 'get_custom_logo_image_attributes', [ $this->instance, 'add_custom_logo_data_hero_candidate_attribute' ] ) );
		$this->assertSame( 10, has_filter( 'get_header_image_tag', [ $this->instance, 'filter_header_image_tag' ] ) );
		$this->assertSame( 10, has_filter( 'wp_get_attachment_image_attributes', [ $this->instance, 'filter_attachment_image_attributes' ] ) );
	}

	/** @covers ::add_custom_logo_data_hero_candidate_attribute()  */
	public function test_add_custom_logo_data_hero_candidate_attribute() {
		$initial_attrs = [ 'data-foo' => 'bar' ];

		$this->assertEquals(
			array_merge(
				$initial_attrs,
				self::ARRAY_WITH_DATA_HERO_CANDIDATE_ATTR
			),
			$this->instance->add_custom_logo_data_hero_candidate_attribute( $initial_attrs ),
			'Expected data-hero-candidate to be injected into first custom logo instance'
		);

		$this->assertEquals(
			$initial_attrs,
			$this->instance->add_custom_logo_data_hero_candidate_attribute( $initial_attrs ),
			'Only expected data-hero-candidate to be injected into first custom logo instance'
		);
	}

	/** @covers ::filter_header_image_tag()  */
	public function test_filter_header_image_tag() {
		$this->assertSame(
			'<img data-hero-candidate src="https://example.com/header.jpg">',
			$this->instance->filter_header_image_tag(
				'<img src="https://example.com/header.jpg">'
			)
		);

		$this->assertSame(
			'<div>Nothing</div>',
			$this->instance->filter_header_image_tag(
				'<div>Nothing</div>'
			)
		);
	}

	/** @covers ::filter_attachment_image_attributes()  */
	public function test_filter_attachment_image_attributes() {
		$attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg', 0 );
		$attachment    = get_post( $attachment_id );
		$post_ids      = [
			self::factory()->post->create(
				[
					'post_date'    => '2021-02-03 04:05:06',
					'post_content' => __FUNCTION__,
				]
			),
			self::factory()->post->create(
				[
					'post_date'    => '2020-01-02 03:04:05',
					'post_content' => __FUNCTION__,
				]
			),
		];

		$search_request_uri = sprintf( '/?s=%s', __FUNCTION__ );

		$initial_attrs = [ 'data-foo' => 'bar' ];
		$merged_attrs  = array_merge(
			$initial_attrs,
			self::ARRAY_WITH_DATA_HERO_CANDIDATE_ATTR
		);

		// Make sure attachment doesn't get data-hero-candidate when it wasn't assigned as a featured image.
		$this->go_to( $search_request_uri );
		$this->assertTrue( is_search() );
		$this->assertSame(
			$post_ids,
			wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ),
			'Expected query to match the posts created.'
		);
		$this->assertEquals(
			$initial_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When attachment is featured image of second post, it still does not get the attribute.
		$this->go_to( $search_request_uri );
		set_post_thumbnail( $post_ids[1], $attachment->ID );
		$this->assertEquals(
			$initial_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When attachment is featured image of first post, it does get the attribute.
		delete_post_thumbnail( $post_ids[1] );
		set_post_thumbnail( $post_ids[0], $attachment->ID );
		$this->go_to( $search_request_uri );
		$this->assertEquals(
			$merged_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When on the page for posts, the the first post in the loop is considered and not the queried object (the page for posts).
		$page_for_posts = self::factory()->post->create( [ 'post_type' => 'page' ] );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_for_posts );
		$this->go_to( get_permalink( $page_for_posts ) );
		$this->assertEquals(
			wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ),
			$post_ids
		);
		$this->assertEquals(
			$merged_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When on the singular post without a featured image, no attribute is added.
		delete_post_thumbnail( $post_ids[0] );
		set_post_thumbnail( $post_ids[1], $attachment->ID );
		$this->go_to( get_permalink( $post_ids[0] ) );
		$this->assertEquals(
			$initial_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When on the singular post has a featured image, an attribute is added.
		delete_post_thumbnail( $post_ids[1] );
		set_post_thumbnail( $post_ids[0], $attachment->ID );
		$this->go_to( get_permalink( $post_ids[0] ) );
		$this->assertEquals(
			$merged_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, $attachment )
		);

		// When no attachment is provided, then no filtering is done.
		delete_post_thumbnail( $post_ids[1] );
		set_post_thumbnail( $post_ids[0], $attachment->ID );
		$this->go_to( get_permalink( $post_ids[0] ) );
		$this->assertEquals(
			$initial_attrs,
			$this->instance->filter_attachment_image_attributes( $initial_attrs, null )
		);
	}

	/** @covers ::add_data_hero_candidate_attribute()  */
	public function test_add_data_hero_candidate_attribute() {
		$this->assertEquals(
			self::ARRAY_WITH_DATA_HERO_CANDIDATE_ATTR,
			$this->instance->add_data_hero_candidate_attribute( [] )
		);

		$initial_attrs = [ 'data-foo' => 'bar' ];
		$this->assertEquals(
			array_merge( $initial_attrs, self::ARRAY_WITH_DATA_HERO_CANDIDATE_ATTR ),
			$this->instance->add_data_hero_candidate_attribute( $initial_attrs )
		);
	}
}
