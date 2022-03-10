<?php
/**
 * Tests for the ValidationDataGarbageCollection class.
 */

namespace AmpProject\AmpWP\Tests\BackgroundTask;

use AMP_Options_Manager;
use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use AmpProject\AmpWP\BackgroundTask\ValidationDataGarbageCollection;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use WP_Post;
use WP_Term;

/** @coversDefaultClass \AmpProject\AmpWP\BackgroundTask\ValidationDataGarbageCollection */
class ValidationDataGarbageCollectionTest extends DependencyInjectedTestCase {

	use PrivateAccess;
	use ValidationRequestMocking;

	/** @var ValidationDataGarbageCollection */
	protected $instance;

	public function set_up() {
		parent::set_up();
		$this->instance = $this->injector->make( ValidationDataGarbageCollection::class );
	}

	/** @covers ::get_interval() */
	public function test_get_interval() {
		$this->assertEquals( 'daily', $this->call_private_method( $this->instance, 'get_interval' ) );
	}

	/** @covers ::get_event_name() */
	public function test_get_event_name() {
		$this->assertEquals( 'amp_validation_data_gc', $this->call_private_method( $this->instance, 'get_event_name' ) );
	}

	/** @covers ::process() */
	public function test_process() {
		$this->add_validate_response_mocking_filter();

		$results = AMP_Validation_Manager::validate_url_and_store( home_url() );

		$post_id = $results['post_id'];
		$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );

		$term_ids = wp_list_pluck( wp_get_post_terms( $post_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ), 'term_id' );
		$this->assertGreaterThan( 0, count( $term_ids ) );

		// Since post is not stale and newer than 1 week, it will remain after processing.
		$this->instance->process();
		$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
		foreach ( $term_ids as $term_id ) {
			$this->assertInstanceOf( WP_Term::class, get_term( $term_id ) );
		}

		// Push date of post back beyond 1 week and make stale.
		wp_update_post(
			[
				'ID'        => $post_id,
				'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '8 days ago' ) ),
			]
		);
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, ! AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) );

		// Processing again will now cause garbage-collection of this post.
		$this->instance->process();
		$this->assertNull( get_post( $post_id ) );
		foreach ( $term_ids as $term_id ) {
			$this->assertNull( get_term( $term_id ) );
		}
	}

	/** @covers ::process() */
	public function test_process_with_filtering() {
		$this->add_validate_response_mocking_filter();

		$results = AMP_Validation_Manager::validate_url_and_store( home_url() );
		$post_id = $results['post_id'];
		$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
		$get_post_term_ids = static function ( $post_id ) {
			return wp_list_pluck( wp_get_post_terms( $post_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ), 'term_id' );
		};
		$term_ids          = $get_post_term_ids( $post_id );
		$this->assertNotEmpty( $term_ids );

		// Push date of post back beyond 1 week and make stale.
		wp_update_post(
			[
				'ID'        => $post_id,
				'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '8 days ago' ) ),
			]
		);
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, ! AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) );

		// Processing should do nothing since URL count is zero.
		add_filter( 'amp_validation_data_gc_url_count', '__return_zero' );
		$this->instance->process();
		$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
		$this->assertEquals( $term_ids, $get_post_term_ids( $post_id ) );

		// Remove count limit and replace with an older before.
		remove_filter( 'amp_validation_data_gc_url_count', '__return_zero' );
		$before_filter = static function () {
			return '14 days ago';
		};
		add_filter( 'amp_validation_data_gc_before', $before_filter );
		$this->instance->process();
		$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
		$this->assertEquals( $term_ids, $get_post_term_ids( $post_id ) );

		// Allow garbage collection of URLs, but don't allow deletion of validation errors.
		remove_filter( 'amp_validation_data_gc_before', $before_filter );
		add_filter( 'amp_validation_data_gc_delete_empty_terms', '__return_false' );
		$this->instance->process();
		$this->assertNull( get_post( $post_id ) );
		foreach ( $term_ids as $term_id ) {
			$this->assertInstanceOf( WP_Term::class, get_term( $term_id ) );
		}

		// Now allow even garbage collection of validation errors.
		remove_filter( 'amp_validation_data_gc_delete_empty_terms', '__return_false' );
		$this->instance->process();
		foreach ( $term_ids as $term_id ) {
			$this->assertNull( get_term( $term_id ) );
		}
	}
}
