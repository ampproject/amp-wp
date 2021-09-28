<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\Option;
use AMP_Options_Manager;
use AMP_Post_Meta_Box;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use AmpProject\AmpWP\Validation\URLScanningContext;
use WP_Query;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\ScannableURLProvider */
final class ScannableURLProviderTest extends TestCase {
	use PrivateAccess, ValidationRequestMocking;

	/**
	 * Validation URL provider instance to use.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [], false ) );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( ScannableURLProvider::class, $this->scannable_url_provider );
	}

	/**
	 * Test retrieval of urls.
	 *
	 * @covers ::get_urls()
	 */
	public function test_count_urls_to_validate() {
		$number_original_urls = 4;

		$this->assertCount( $number_original_urls, $this->scannable_url_provider->get_urls() );

		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 100 ) );

		$category         = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$number_new_posts = 50;
		$post_ids         = [];
		for ( $i = 0; $i < $number_new_posts; $i++ ) {
			$post_ids[] = self::factory()->post->create(
				[
					'tax_input' => [ 'category' => $category ],
				]
			);
		}

		/*
		 * Add the number of new posts, original URLs, and 1 for the $category that all of them have.
		 * And ensure that the tested method finds a URL for all of them.
		 */
		$expected_url_count = $number_new_posts + $number_original_urls + 1;
		$this->assertCount( $expected_url_count, $this->scannable_url_provider->get_urls() );

		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 100 ) );

		$number_of_new_terms        = 20;
		$expected_url_count        += $number_of_new_terms;
		$taxonomy                   = 'category';
		$terms_for_current_taxonomy = [];
		for ( $i = 0; $i < $number_of_new_terms; $i++ ) {
			$terms_for_current_taxonomy[] = self::factory()->term->create(
				[
					'taxonomy' => $taxonomy,
				]
			);
		}

		// Terms need to be associated with a post in order to be returned in get_terms().
		$result = wp_set_post_terms(
			$post_ids[0],
			$terms_for_current_taxonomy,
			$taxonomy
		);
		$this->assertFalse( is_wp_error( $result ) );

		$this->assertCount( $expected_url_count, $this->scannable_url_provider->get_urls() );
	}

	/**
	 * Test get_posts_that_support_amp.
	 *
	 * @covers ::get_posts_that_support_amp()
	 */
	public function test_get_posts_that_support_amp() {
		$number_of_posts = 20;
		$ids             = [];
		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$ids[] = self::factory()->post->create();
		}

		// This should count all of the newly-created posts as supporting AMP.
		$this->assertEquals( $ids, $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) );

		// Simulate 'Enable AMP' being unchecked in the post editor, in which case get_url_count() should not count it.
		$first_id = $ids[0];
		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::DISABLED_STATUS
		);
		$this->assertEquals( [], $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ [ $first_id ] ] ) );

		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::ENABLED_STATUS
		);

		// When the second $force_count_all_urls argument is true, all of the newly-created posts should be part of the URL count.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [], true ) );
		$this->assertEquals( $ids, $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) );
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [], false ) );

		// In AMP-first, the IDs should include all of the newly-created posts.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertEquals( $ids, $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) );

		// In Transitional Mode, the IDs should also include all of the newly-created posts.
		add_theme_support(
			AMP_Theme_Support::SLUG,
			[
				AMP_Theme_Support::PAIRED_FLAG => true,
			]
		);
		$this->assertEquals( $ids, $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) );

		/*
		 * If the WP-CLI command has an include argument, and is_singular isn't in it, no posts will have AMP enabled.
		 * For example, wp amp validate-site --include=is_tag,is_category
		 */
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_tag', 'is_category' ], false ) );
		$this->assertEquals( [], $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) );

		/*
		 * If is_singular is in the WP-CLI argument, it should return these posts as being AMP-enabled.
		 * For example, wp amp validate-site include=is_singular,is_category
		 */
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_singular', 'is_category' ], false ) );
		$this->assertEmpty( array_diff( $ids, $this->call_private_method( $this->scannable_url_provider, 'get_posts_that_support_amp', [ $ids ] ) ) );
	}

	/**
	 * Test get_author_page_urls.
	 *
	 * @covers ::get_author_page_urls()
	 */
	public function test_get_author_page_urls() {
		self::factory()->user->create();
		$users             = get_users();
		$first_author      = $users[0];
		$first_author_url  = get_author_posts_url( $first_author->ID, $first_author->user_nicename );
		$second_author     = $users[1];
		$second_author_url = get_author_posts_url( $second_author->ID, $second_author->user_nicename );

		$actual_urls = $this->call_private_method( $this->scannable_url_provider, 'get_author_page_urls', [ 0, 1 ] );

		// Passing 0 as the offset argument should get the first author.
		$this->assertEquals( [ $first_author_url ], $actual_urls );

		$actual_urls = $this->call_private_method( $this->scannable_url_provider, 'get_author_page_urls', [ 1, 1 ] );

		// Passing 1 as the offset argument should get the second author.
		$this->assertEquals( [ $second_author_url ], $actual_urls );

		// If $include_conditionals is set and does not have is_author, this should not return a URL.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_category' ], false ) );
		$this->assertEquals( [], $this->call_private_method( $this->scannable_url_provider, 'get_author_page_urls' ) );

		// If $include_conditionals is set and has is_author, this should return URLs.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_author' ], false ) );
		$this->assertEquals(
			[ $first_author_url, $second_author_url ],
			$this->call_private_method( $this->scannable_url_provider, 'get_author_page_urls' )
		);
	}

	/**
	 * Test does_taxonomy_support_amp.
	 *
	 * @covers ::does_taxonomy_support_amp()
	 */
	public function test_does_taxonomy_support_amp() {
		$custom_taxonomy = 'foo_custom_taxonomy';
		register_taxonomy( $custom_taxonomy, 'post' );
		$taxonomies_to_test = [ $custom_taxonomy, 'category', 'post_tag' ];
		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ 'is_category', 'is_tag', sprintf( 'is_tax[%s]', $custom_taxonomy ) ] );

		// When these templates are not unchecked in the 'AMP Settings' UI, these should be supported.
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ $taxonomy ] ) );
		}

		// When the user has not checked the boxes for 'Categories' and 'Tags,' this should be false.
		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ 'is_author' ] );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertFalse( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ $taxonomy ] ) );
		}

		// When $include_unsupported is true, all taxonomies should be supported.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [], true ) );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ $taxonomy ] ) );
		}
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [], false ) );

		// When the user has checked the Option::ALL_TEMPLATES_SUPPORTED box, this should always be true.
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, true );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ $taxonomy ] ) );
		}
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );

		/*
		 * If the user passed allowed conditionals to the WP-CLI command like wp amp validate-site --include=is_category,is_tag
		 * these should be supported taxonomies.
		 */
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_category', 'is_tag' ], true ) );
		$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ 'category' ] ) );
		$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ 'tag' ] ) );
		$this->assertFalse( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ 'author' ] ) );
		$this->assertFalse( $this->call_private_method( $this->scannable_url_provider, 'does_taxonomy_support_amp', [ 'search' ] ) );
	}

	/**
	 * Test is_template_supported.
	 *
	 * @covers ::is_template_supported()
	 */
	public function test_is_template_supported() {
		$author_conditional = 'is_author';
		$search_conditional = 'is_search';

		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ $author_conditional ] );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'is_template_supported', [ $author_conditional ] ) );
		$this->assertFalse( $this->call_private_method( $this->scannable_url_provider, 'is_template_supported', [ $search_conditional ] ) );

		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ $search_conditional ] );
		$this->assertTrue( $this->call_private_method( $this->scannable_url_provider, 'is_template_supported', [ $search_conditional ] ) );
		$this->assertFalse( $this->call_private_method( $this->scannable_url_provider, 'is_template_supported', [ $author_conditional ] ) );
	}

	/**
	 * Test get_posts_by_type.
	 *
	 * @covers ::get_posts_by_type()
	 */
	public function test_get_posts_by_type() {
		$number_posts_each_post_type = 20;
		$post_types                  = get_post_types( [ 'public' => true ], 'names' );

		foreach ( $post_types as $post_type ) {
			// Start the expected posts with the existing post(s).
			$query          = new WP_Query(
				[
					'fields'    => 'ids',
					'post_type' => $post_type,
				]
			);
			$expected_posts = $query->posts;

			for ( $i = 0; $i < $number_posts_each_post_type; $i++ ) {
				array_unshift(
					$expected_posts,
					self::factory()->post->create(
						[
							'post_type' => $post_type,
						]
					)
				);
			}

			$actual_posts = $this->call_private_method( $this->scannable_url_provider, 'get_posts_by_type', [ $post_type ] );
			$this->assertEquals( $expected_posts, array_values( $actual_posts ) );

			// Test with the $offset and $number arguments.
			$offset       = 0;
			$actual_posts = $this->call_private_method( $this->scannable_url_provider, 'get_posts_by_type', [ $post_type, $offset, $number_posts_each_post_type ] );
			$this->assertEquals( array_slice( $expected_posts, $offset, $number_posts_each_post_type ), $actual_posts );
		}
	}

	/**
	 * Test get_taxonomy_links.
	 *
	 * @covers ::get_taxonomy_links()
	 */
	public function test_get_taxonomy_links() {
		$number_links_each_taxonomy = 20;
		$taxonomies                 = get_taxonomies(
			[
				'public' => true,
			]
		);

		foreach ( $taxonomies as $taxonomy ) {
			// Begin the expected links with the term links that already exist.
			$expected_links             = array_map( 'get_term_link', get_terms( [ 'taxonomy' => $taxonomy ] ) );
			$terms_for_current_taxonomy = [];
			for ( $i = 0; $i < $number_links_each_taxonomy; $i++ ) {
				$terms_for_current_taxonomy[] = self::factory()->term->create(
					[
						'taxonomy' => $taxonomy,
					]
				);
			}

			// Terms need to be associated with a post in order to be returned in get_terms().
			$result = wp_set_post_terms(
				self::factory()->post->create(),
				$terms_for_current_taxonomy,
				$taxonomy
			);
			$this->assertFalse( is_wp_error( $result ) );

			$expected_links  = array_merge(
				$expected_links,
				array_map( 'get_term_link', $terms_for_current_taxonomy )
			);
			$number_of_links = 100;
			$actual_links    = $this->call_private_method( $this->scannable_url_provider, 'get_taxonomy_links', [ $taxonomy, 0, $number_of_links ] );

			// The get_terms() call in get_taxonomy_links() returns an array with a first index of 1, so correct for that with array_values().
			$this->assertEquals( $expected_links, array_values( $actual_links ) );
			$this->assertLessThan( $number_of_links, count( $actual_links ) );

			$number_of_links           = 5;
			$offset                    = 10;
			$actual_links_using_offset = $this->call_private_method( $this->scannable_url_provider, 'get_taxonomy_links', [ $taxonomy, $offset, $number_of_links ] );
			$this->assertEquals( array_slice( $expected_links, $offset, $number_of_links ), array_values( $actual_links_using_offset ) );
			$this->assertEquals( $number_of_links, count( $actual_links_using_offset ) );
		}
	}

	/**
	 * Test get_search_page.
	 *
	 * @covers ::get_search_page()
	 */
	public function test_get_search_page() {
		// Normally, this should return a string, unless the user has opted out of the search template.
		$this->assertTrue( is_string( $this->call_private_method( $this->scannable_url_provider, 'get_search_page' ) ) );

		// If $include_conditionals is set and does not have is_search, this should not return a URL.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_author' ], false ) );
		$this->assertEquals( null, $this->call_private_method( $this->scannable_url_provider, 'get_search_page' ) );

		// If $include_conditionals has is_search, this should return a URL.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_search' ], false ) );
		$this->assertTrue( is_string( $this->call_private_method( $this->scannable_url_provider, 'get_search_page' ) ) );
	}

	/**
	 * Test get_date_page.
	 *
	 * @covers ::get_date_page()
	 */
	public function test_get_date_page() {
		$year = gmdate( 'Y' );

		// Normally, this should return the date page, unless the user has opted out of that template.
		$this->assertStringContainsString( $year, $this->call_private_method( $this->scannable_url_provider, 'get_date_page' ) );

		// If $include_conditionals is set and does not have is_date, this should not return a URL.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_search' ], false ) );
		$this->assertEquals( null, $this->call_private_method( $this->scannable_url_provider, 'get_date_page' ) );

		// If $include_conditionals has is_date, this should return a URL.
		$this->scannable_url_provider = new ScannableURLProvider( new URLScanningContext( 20, [ 'is_date' ], false ) );
		$parsed_page_url              = wp_parse_url( $this->call_private_method( $this->scannable_url_provider, 'get_date_page' ) );
		$this->assertStringContainsString( $year, $parsed_page_url['query'] );
	}
}
