<?php
/**
 * Tests for AMP_CLI class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_CLI class.
 *
 * @since 1.0
 */
class Test_AMP_CLI extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'add_comment' ] );
		AMP_CLI::$include_conditionals      = [];
		AMP_CLI::$limit_type_validate_count = 100;
	}

	/**
	 * Resets the state after a test method is called.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		AMP_CLI::$total_errors = 0;
		parent::tearDown();
	}

	/**
	 * Test count_urls_to_validate.
	 *
	 * @covers AMP_CLI::count_urls_to_validate()
	 */
	public function test_count_urls_to_validate() {
		// The number of original URLs present before adding these test URLs.
		$number_original_urls = $this->get_inital_url_count();
		$this->assertEquals( $number_original_urls, AMP_CLI::count_urls_to_validate() );
		AMP_CLI::$limit_type_validate_count = 100;

		$category         = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$number_new_posts = AMP_CLI::$limit_type_validate_count / 2;
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
		$this->assertEquals( $expected_url_count, AMP_CLI::count_urls_to_validate() );

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
		wp_set_post_terms(
			$post_ids[0],
			$terms_for_current_taxonomy,
			$taxonomy
		);

		$this->assertEquals( $expected_url_count, AMP_CLI::count_urls_to_validate() );
	}

	/**
	 * Test get_posts_that_support_amp.
	 *
	 * @covers AMP_CLI::get_posts_that_support_amp()
	 */
	public function test_get_posts_that_support_amp() {
		$number_of_posts = 20;
		$ids             = [];
		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$ids[] = self::factory()->post->create();
		}

		// This should count all of the newly-created posts as supporting AMP.
		$this->assertEquals( $ids, AMP_CLI::get_posts_that_support_amp( $ids ) );

		// Simulate 'Enable AMP' being unchecked in the post editor, in which case get_url_count() should not count it.
		$first_id = $ids[0];
		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::DISABLED_STATUS
		);
		$this->assertEquals( [], AMP_CLI::get_posts_that_support_amp( [ $first_id ] ) );

		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::ENABLED_STATUS
		);

		// When the second $force_count_all_urls argument is true, all of the newly-created posts should be part of the URL count.
		AMP_CLI::$force_crawl_urls = true;
		$this->assertEquals( $ids, AMP_CLI::get_posts_that_support_amp( $ids ) );
		AMP_CLI::$force_crawl_urls = false;

		// In AMP-first, the IDs should include all of the newly-created posts.
		add_theme_support( AMP_Theme_Support::SLUG );
		$this->assertEquals( $ids, AMP_CLI::get_posts_that_support_amp( $ids ) );

		// In Transitional Mode, the IDs should also include all of the newly-created posts.
		add_theme_support(
			AMP_Theme_Support::SLUG,
			[
				AMP_Theme_Support::PAIRED_FLAG => true,
			]
		);
		$this->assertEquals( $ids, AMP_CLI::get_posts_that_support_amp( $ids ) );

		/*
		 * If the WP-CLI command has an include argument, and is_singular isn't in it, no posts will have AMP enabled.
		 * For example, wp amp validate-site --include=is_tag,is_category
		 */
		AMP_CLI::$include_conditionals = [ 'is_tag', 'is_category' ];
		$this->assertEquals( [], AMP_CLI::get_posts_that_support_amp( $ids ) );

		/*
		 * If is_singular is in the WP-CLI argument, it should allow return these posts as being AMP-enabled.
		 * For example, wp amp validate-site include=is_singular,is_category
		 */
		AMP_CLI::$include_conditionals = [ 'is_singular', 'is_category' ];
		$this->assertEmpty( array_diff( $ids, AMP_CLI::get_posts_that_support_amp( $ids ) ) );
		AMP_CLI::$include_conditionals = [];
	}

	/**
	 * Test does_taxonomy_support_amp.
	 *
	 * @covers AMP_CLI::does_taxonomy_support_amp()
	 */
	public function test_does_taxonomy_support_amp() {
		$custom_taxonomy = 'foo_custom_taxonomy';
		register_taxonomy( $custom_taxonomy, 'post' );
		$taxonomies_to_test = [ $custom_taxonomy, 'category', 'post_tag' ];
		AMP_Options_Manager::update_option( 'supported_templates', [ 'is_category', 'is_tag', sprintf( 'is_tax[%s]', $custom_taxonomy ) ] );

		// When these templates are not unchecked in the 'AMP Settings' UI, these should be supported.
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_CLI::does_taxonomy_support_amp( $taxonomy ) );
		}

		// When the user has not checked the boxes for 'Categories' and 'Tags,' this should be false.
		AMP_Options_Manager::update_option( 'supported_templates', [ 'is_author' ] );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertFalse( AMP_CLI::does_taxonomy_support_amp( $taxonomy ) );
		}

		// When $force_crawl_urls is true, all taxonomies should be supported.
		AMP_CLI::$force_crawl_urls = true;
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_CLI::does_taxonomy_support_amp( $taxonomy ) );
		}
		AMP_CLI::$force_crawl_urls = false;

		// When the user has checked the 'all_templates_supported' box, this should always be true.
		AMP_Options_Manager::update_option( 'all_templates_supported', true );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_CLI::does_taxonomy_support_amp( $taxonomy ) );
		}
		AMP_Options_Manager::update_option( 'all_templates_supported', false );

		/*
		 * If the user passed allowed conditionals to the WP-CLI command like wp amp validate-site --include=is_category,is_tag
		 * these should be supported taxonomies.
		 */
		AMP_CLI::$include_conditionals = [ 'is_category', 'is_tag' ];
		$this->assertTrue( AMP_CLI::does_taxonomy_support_amp( 'category' ) );
		$this->assertTrue( AMP_CLI::does_taxonomy_support_amp( 'tag' ) );
		$this->assertFalse( AMP_CLI::does_taxonomy_support_amp( 'author' ) );
		$this->assertFalse( AMP_CLI::does_taxonomy_support_amp( 'search' ) );
		AMP_CLI::$include_conditionals = [];
	}

	/**
	 * Test is_template_supported.
	 *
	 * @covers AMP_CLI::is_template_supported()
	 */
	public function test_is_template_supported() {
		$author_conditional = 'is_author';
		$search_conditional = 'is_search';

		AMP_Options_Manager::update_option( 'supported_templates', [ $author_conditional ] );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		$this->assertTrue( AMP_CLI::is_template_supported( $author_conditional ) );
		$this->assertFalse( AMP_CLI::is_template_supported( $search_conditional ) );

		AMP_Options_Manager::update_option( 'supported_templates', [ $search_conditional ] );
		$this->assertTrue( AMP_CLI::is_template_supported( $search_conditional ) );
		$this->assertFalse( AMP_CLI::is_template_supported( $author_conditional ) );
	}

	/**
	 * Test get_posts_by_type.
	 *
	 * @covers AMP_CLI::get_posts_by_type()
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

			$actual_posts = AMP_CLI::get_posts_by_type( $post_type );
			$this->assertEquals( $expected_posts, array_values( $actual_posts ) );

			// Test with the $offset and $number arguments.
			$offset       = 0;
			$actual_posts = AMP_CLI::get_posts_by_type( $post_type, $offset, $number_posts_each_post_type );
			$this->assertEquals( array_slice( $expected_posts, $offset, $number_posts_each_post_type ), $actual_posts );
		}
	}

	/**
	 * Test get_taxonomy_links.
	 *
	 * @covers AMP_CLI::get_taxonomy_links()
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
			wp_set_post_terms(
				self::factory()->post->create(),
				$terms_for_current_taxonomy,
				$taxonomy
			);

			$expected_links  = array_merge(
				$expected_links,
				array_map( 'get_term_link', $terms_for_current_taxonomy )
			);
			$number_of_links = 100;
			$actual_links    = AMP_CLI::get_taxonomy_links( $taxonomy, 0, $number_of_links );

			// The get_terms() call in get_taxonomy_links() returns an array with a first index of 1, so correct for that with array_values().
			$this->assertEquals( $expected_links, array_values( $actual_links ) );
			$this->assertLessThan( $number_of_links, count( $actual_links ) );

			$number_of_links           = 5;
			$offset                    = 10;
			$actual_links_using_offset = AMP_CLI::get_taxonomy_links( $taxonomy, $offset, $number_of_links );
			$this->assertEquals( array_slice( $expected_links, $offset, $number_of_links ), array_values( $actual_links_using_offset ) );
			$this->assertCount( $number_of_links, $actual_links_using_offset );
		}
	}

	/**
	 * Test get_author_page_urls.
	 *
	 * @covers AMP_CLI::get_author_page_urls()
	 */
	public function test_get_author_page_urls() {
		self::factory()->user->create();
		$users             = get_users();
		$first_author      = $users[0];
		$first_author_url  = get_author_posts_url( $first_author->ID, $first_author->user_nicename );
		$second_author     = $users[1];
		$second_author_url = get_author_posts_url( $second_author->ID, $second_author->user_nicename );

		// Passing 0 as the offset argument should get the first author.
		$this->assertEquals( [ $first_author_url ], $actual_urls = AMP_CLI::get_author_page_urls( 0, 1 ) );

		// Passing 1 as the offset argument should get the second author.
		$this->assertEquals( [ $second_author_url ], $actual_urls = AMP_CLI::get_author_page_urls( 1, 1 ) );

		// If $include_conditionals is set and does not have is_author, this should not return a URL.
		AMP_CLI::$include_conditionals = [ 'is_category' ];
		$this->assertEquals( [], AMP_CLI::get_author_page_urls() );

		// If $include_conditionals is set and has is_author, this should return URLs.
		AMP_CLI::$include_conditionals = [ 'is_author' ];
		$this->assertEquals(
			[ $first_author_url, $second_author_url ],
			AMP_CLI::get_author_page_urls()
		);
		AMP_CLI::$include_conditionals = [];
	}

	/**
	 * Test get_search_page.
	 *
	 * @covers AMP_CLI::get_search_page()
	 */
	public function test_get_search_page() {
		// Normally, this should return a string, unless the user has opted out of the search template.
		$this->assertInternalType( 'string', AMP_CLI::get_search_page() );

		// If $include_conditionals is set and does not have is_search, this should not return a URL.
		AMP_CLI::$include_conditionals = [ 'is_author' ];
		$this->assertEquals( null, AMP_CLI::get_search_page() );

		// If $include_conditionals has is_search, this should return a URL.
		AMP_CLI::$include_conditionals = [ 'is_search' ];
		$this->assertInternalType( 'string', AMP_CLI::get_search_page() );
		AMP_CLI::$include_conditionals = [];
	}

	/**
	 * Test get_date_page.
	 *
	 * @covers AMP_CLI::get_date_page()
	 */
	public function test_get_date_page() {
		$year = date( 'Y' );

		// Normally, this should return the date page, unless the user has opted out of that template.
		$this->assertContains( $year, AMP_CLI::get_date_page() );

		// If $include_conditionals is set and does not have is_date, this should not return a URL.
		AMP_CLI::$include_conditionals = [ 'is_search' ];
		$this->assertEquals( null, AMP_CLI::get_date_page() );

		// If $include_conditionals has is_date, this should return a URL.
		AMP_CLI::$include_conditionals = [ 'is_date' ];
		$parsed_page_url               = wp_parse_url( AMP_CLI::get_date_page() );
		$this->assertContains( $year, $parsed_page_url['query'] );
		AMP_CLI::$include_conditionals = [];
	}

	/**
	 * Test crawl_site.
	 *
	 * @covers AMP_CLI::crawl_site()
	 */
	public function test_crawl_site() {
		$number_of_posts = 20;
		$number_of_terms = 30;
		$posts           = [];
		$post_permalinks = [];
		$terms           = [];

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$post_id           = self::factory()->post->create();
			$posts[]           = $post_id;
			$post_permalinks[] = get_permalink( $post_id );
		}
		AMP_CLI::crawl_site();

		// All of the posts created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $post_permalinks, $this->get_validated_urls() ) );

		for ( $i = 0; $i < $number_of_terms; $i++ ) {
			$terms[] = self::factory()->category->create();
		}

		// Terms need to be associated with a post in order to be returned in get_terms().
		wp_set_post_terms( $posts[0], $terms, 'category' );
		AMP_CLI::crawl_site();
		$expected_validated_urls = array_map( 'get_term_link', $terms );
		$actual_validated_urls   = $this->get_validated_urls();

		// All of the terms created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $expected_validated_urls, $actual_validated_urls ) );
		$this->assertContains( home_url( '/' ), $this->get_validated_urls() );
	}

	/**
	 * Test validate_and_store_url.
	 *
	 * @covers AMP_CLI::validate_and_store_url()
	 */
	public function test_validate_and_store_url() {
		$single_post_permalink = get_permalink( self::factory()->post->create() );
		AMP_CLI::validate_and_store_url( $single_post_permalink, 'post' );
		$this->assertContains( $single_post_permalink, $this->get_validated_urls() );

		$number_of_posts = 30;
		$post_permalinks = [];

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$permalink         = get_permalink( self::factory()->post->create() );
			$post_permalinks[] = $permalink;
			AMP_CLI::validate_and_store_url( $permalink, 'post' );
		}

		// All of the posts created should be present in the validated URLs.
		$this->assertEmpty( array_diff( $post_permalinks, $this->get_validated_urls() ) );
	}

	/**
	 * Gets the initial count of URLs on the site.
	 *
	 * @return int The initial count of URLs.
	 */
	public function get_inital_url_count() {
		$total_count  = 'posts' === get_option( 'show_on_front' ) ? 1 : 0;
		$post_query   = new WP_Query( [ 'post_type' => get_post_types( [ 'public' => true ], 'names' ) ] );
		$total_count += $post_query->found_posts;

		$term_query = new WP_Term_Query(
			[
				'taxonomy' => get_taxonomies( [ 'public' => true ] ),
				'fields'   => 'ids',
			]
		);

		$total_count += count( $term_query->terms );
		$total_count += count( AMP_CLI::get_author_page_urls() );
		$total_count += is_string( AMP_CLI::get_search_page() ) ? 1 : 0;
		$total_count += is_string( AMP_CLI::get_date_page() ) ? 1 : 0;

		return $total_count;
	}

	/**
	 * Gets all of the validated URLs.
	 *
	 * @return string[] $urls The validated URLs.
	 */
	public function get_validated_urls() {
		$query = new WP_Query(
			[
				'post_type'      => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'posts_per_page' => 100,
				'fields'         => 'ids',
			]
		);

		return array_map(
			static function( $post ) {
				return remove_query_arg( 'amp', AMP_Validated_URL_Post_Type::get_url_from_post( $post ) );
			},
			$query->posts
		);
	}

	/**
	 * Adds the AMP_VALIDATION: comment to the <html> body.
	 *
	 * @return array The response, with a comment in the body.
	 */
	public function add_comment() {
		$mock_validation = [
			'results' => [
				[
					'error'     => [
						'code' => 'foo',
					],
					'sanitized' => false,
				],
			],
			'url'     => home_url( '/' ),
		];

		return [
			'body'     => sprintf(
				'<html amp><head></head><body></body><!--%s--></html>',
				'AMP_VALIDATION:' . wp_json_encode( $mock_validation )
			),
			'response' => [
				'code'    => 200,
				'message' => 'ok',
			],
		];
	}
}
