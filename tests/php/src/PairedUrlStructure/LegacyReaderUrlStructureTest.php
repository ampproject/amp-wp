<?php

namespace AmpProject\AmpWP\Tests\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure */
class LegacyReaderUrlStructureTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/** @var LegacyReaderUrlStructure */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' ); // Needed for user_trailingslashit().
		$this->instance = $this->injector->make( LegacyReaderUrlStructure::class );
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint_non_post_and_post() {
		$slug = amp_get_slug();

		$post_id = self::factory()->post->create();

		$amp_pre_get_permalink_count = 0;
		add_filter(
			'amp_pre_get_permalink',
			static function ( $pre ) use ( &$amp_pre_get_permalink_count ) {
				$amp_pre_get_permalink_count++;
				return $pre;
			}
		);

		$amp_get_permalink_count = 0;
		add_filter(
			'amp_get_permalink',
			static function ( $pre ) use ( &$amp_get_permalink_count ) {
				$amp_get_permalink_count++;
				return $pre;
			}
		);

		// Non-post URL.
		$year_archive_url     = home_url( '/2020/' );
		$amp_year_archive_url = $this->instance->add_endpoint( $year_archive_url );
		$this->assertEquals( 0, $amp_pre_get_permalink_count );
		$this->assertEquals( 0, $amp_get_permalink_count );
		$this->assertEquals( home_url( "/2020/?$slug" ), $amp_year_archive_url );

		// Post URL without filtering.
		$post_permalink_url = get_permalink( $post_id );
		$this->assertEquals(
			trailingslashit( trailingslashit( $post_permalink_url ) . $slug ),
			$this->instance->add_endpoint( $post_permalink_url )
		);
		$this->assertEquals( 1, $amp_pre_get_permalink_count );
		$this->assertEquals( 1, $amp_get_permalink_count );

		// Try overriding a post URL with pre-filter.
		$pre_post_permalink_url       = $post_permalink_url . 'pre-filter-amp/';
		$filter_amp_pre_get_permalink = function ( $pre, $filtered_post_id ) use ( $post_id, $pre_post_permalink_url ) {
			$this->assertEquals( null, $pre );
			$this->assertEquals( $post_id, $filtered_post_id );
			return $pre_post_permalink_url;
		};
		add_filter(
			'amp_pre_get_permalink',
			$filter_amp_pre_get_permalink,
			10,
			2
		);
		$this->assertEquals(
			$pre_post_permalink_url,
			$this->instance->add_endpoint( $post_permalink_url )
		);
		$this->assertEquals( 2, $amp_pre_get_permalink_count );
		$this->assertEquals( 1, $amp_get_permalink_count );
		remove_filter( 'amp_pre_get_permalink', $filter_amp_pre_get_permalink, 10 );

		// Try overriding a post URL with post-filter.
		$expected_original_amp_url = trailingslashit( trailingslashit( $post_permalink_url ) . $slug );
		$return_post_permalink_url = $post_permalink_url . 'post-filter-amp/';
		$filter_amp_get_permalink  = function ( $amp_url, $filtered_post_id ) use ( $post_id, $return_post_permalink_url, $expected_original_amp_url ) {
			$this->assertEquals( $expected_original_amp_url, $amp_url );
			$this->assertEquals( $post_id, $filtered_post_id );
			return $return_post_permalink_url;
		};
		add_filter(
			'amp_get_permalink',
			$filter_amp_get_permalink,
			10,
			2
		);
		$this->assertEquals(
			$return_post_permalink_url,
			$this->instance->add_endpoint( $post_permalink_url )
		);
		$this->assertEquals( 3, $amp_pre_get_permalink_count );
		$this->assertEquals( 2, $amp_get_permalink_count );
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint_for_page_and_attachment() {
		$slug = amp_get_slug();

		$page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );
		$this->assertEquals(
			get_permalink( $page_id ) . "?{$slug}",
			$this->instance->add_endpoint( get_permalink( $page_id ) )
		);

		$attachment_id = self::factory()->post->create( [ 'post_type' => 'attachment' ] );
		$this->assertEquals(
			get_permalink( $attachment_id ) . "?{$slug}",
			$this->instance->add_endpoint( get_permalink( $attachment_id ) )
		);
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint_when_permalink_has_query_parameter() {
		$slug      = amp_get_slug();
		$post_id   = self::factory()->post->create();
		$permalink = get_permalink( $post_id );

		add_filter(
			'post_link',
			static function ( $url ) {
				return add_query_arg( 'foo', 'bar', $url );
			}
		);

		$this->assertEquals(
			"$permalink?foo=bar&{$slug}",
			$this->instance->add_endpoint( get_permalink( $post_id ) )
		);
	}

	/** @covers ::has_endpoint() */
	public function test_has_endpoint() {
		$slug = amp_get_slug();
		$this->assertFalse( $this->instance->has_endpoint( home_url( '/foo/' ) ) );
		$this->assertTrue( $this->instance->has_endpoint( home_url( "/foo/?{$slug}" ) ) );
		$this->assertTrue( $this->instance->has_endpoint( home_url( "/foo/$slug/" ) ) );
	}

	/** @covers ::remove_endpoint() */
	public function test_remove_endpoint() {
		$slug = amp_get_slug();
		$this->assertEquals(
			home_url( '/foo/' ),
			$this->instance->remove_endpoint( home_url( "/foo/?{$slug}" ) )
		);
		$this->assertEquals(
			home_url( '/foo/' ),
			$this->instance->remove_endpoint( home_url( "/foo/$slug/" ) )
		);
	}

	/** @covers ::url_to_postid() */
	public function test_url_to_postid() {
		$this->assertEquals(
			0,
			$this->call_private_method( $this->instance, 'url_to_postid', [ 'https://external.example.com/' ] )
		);

		$url_to_postid_filter_count = 0;
		add_filter(
			'url_to_postid',
			static function ( $url ) use ( &$url_to_postid_filter_count ) {
				$url_to_postid_filter_count++;
				return $url;
			}
		);

		$post_id = self::factory()->post->create();

		for ( $i = 0; $i < 5; $i++ ) {
			$this->assertEquals(
				$post_id,
				$this->call_private_method( $this->instance, 'url_to_postid', [ get_permalink( $post_id ) ] )
			);
		}
		$this->assertEquals( 1, $url_to_postid_filter_count );
	}
}
