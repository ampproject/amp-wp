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

	public function setUp() {
		parent::setUp();
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
}
