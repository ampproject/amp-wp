<?php
/**
 * Tests for AMP_Site_Validation class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Site_Validation class.
 *
 * @since 1.0
 */
class Test_AMP_Site_Validation extends \WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		add_filter( 'pre_http_request', array( $this, 'add_comment' ) );
	}

	/**
	 * Resets the state after a test method is called.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		AMP_Site_Validation::$total_errors = 0;
		parent::tearDown();
	}

	/**
	 * Test count_posts_and_terms.
	 *
	 * @covers AMP_Site_Validation::count_posts_and_terms()
	 */
	public function test_count_posts_and_terms() {
		// The number of original URLs present before adding these test URLs.
		$number_original_urls = $this->get_inital_url_count();
		$this->assertEquals( $number_original_urls, AMP_Site_Validation::count_posts_and_terms() );

		$category         = $this->factory()->term->create( array( 'taxonomy' => 'category' ) );
		$number_new_posts = AMP_Site_Validation::BATCH_SIZE * 3;
		$post_ids         = array();
		for ( $i = 0; $i < $number_new_posts; $i++ ) {
			$post_ids[] = $this->factory()->post->create( array(
				'tax_input' => array( 'category' => $category ),
			) );
		}

		/**
		 * Add the number of new posts, original URLs, and 1 for the $category that all of them have.
		 * And ensure that the tested method finds a URL for all of them.
		 */
		$expected_url_count = $number_new_posts + $number_original_urls + 1;
		$this->assertEquals( $expected_url_count, AMP_Site_Validation::count_posts_and_terms() );

		$number_of_new_terms        = 20;
		$expected_url_count        += $number_of_new_terms;
		$taxonomy                   = 'category';
		$terms_for_current_taxonomy = array();
		for ( $i = 0; $i < $number_of_new_terms; $i++ ) {
			$terms_for_current_taxonomy[] = $this->factory()->term->create( array(
				'taxonomy' => $taxonomy,
			) );
		}

		// Terms need to be associated with a post in order to be returned in get_terms().
		wp_set_post_terms(
			$post_ids[0],
			$terms_for_current_taxonomy,
			$taxonomy
		);

		$this->assertEquals( $expected_url_count, AMP_Site_Validation::count_posts_and_terms() );
	}

	/**
	 * Test get_posts_that_support_amp.
	 *
	 * @covers AMP_Site_Validation::get_posts_that_support_amp()
	 */
	public function test_get_posts_that_support_amp() {
		$number_of_posts = 20;
		$ids             = array();
		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$ids[] = $this->factory()->post->create();
		}

		// This should count all of the newly-created posts as supporting AMP (when you add the query ary).
		$this->assertEquals( $ids, AMP_Site_Validation::get_posts_that_support_amp( $ids ) );

		// Simulate 'Enable AMP' being unchecked in the post editor, in which case get_url_count() should not count it.
		$first_id = $ids[0];
		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::DISABLED_STATUS
		);
		$this->assertEquals( array(), AMP_Site_Validation::get_posts_that_support_amp( array( $first_id ) ) );

		update_post_meta(
			$first_id,
			AMP_Post_Meta_Box::STATUS_POST_META_KEY,
			AMP_Post_Meta_Box::ENABLED_STATUS
		);

		/**
		 * When the second $force_count_all_urls argument is true, all of the newly-created posts should be part of the URL count,
		 * even though they're not AMP endpoints.
		 */
		AMP_Site_Validation::$force_crawl_all_urls = true;
		$this->assertEquals( $ids, AMP_Site_Validation::get_posts_that_support_amp( $ids, true ) );
		AMP_Site_Validation::$force_crawl_all_urls = false;

		// In Native AMP, the URL count should include all of the newly-created posts.
		add_theme_support( 'amp' );
		$this->assertEquals( $ids, AMP_Site_Validation::get_posts_that_support_amp( $ids ) );

		// In Paired Mode, the URL count should also include all of the newly-created posts.
		add_theme_support( 'amp', array(
			'paired' => true,
		) );
		$this->assertEquals( $ids, AMP_Site_Validation::get_posts_that_support_amp( $ids ) );

		/**
		 * If the WP-CLI command has an include argument, and is_singular isn't in it, no posts will have AMP enabled.
		 * For example, wp amp validate-site include=is_tag,is_category
		 */
		AMP_Site_Validation::$include_conditionals = array( 'is_tag', 'is_category' );
		$this->assertEquals( array(), AMP_Site_Validation::get_posts_that_support_amp( $ids ) );

		/*
		 * If is_singular is in the WP-CLI argument, it should allow return these posts as being AMP-enabled.
		 * For example, wp amp validate-site include=is_singular,is_category
		 */
		AMP_Site_Validation::$include_conditionals = array( 'is_singular', 'is_category' );
		$this->assertEquals( $ids, AMP_Site_Validation::get_posts_that_support_amp( $ids ) );
	}

	/**
	 * Test does_taxonomy_support_amp.
	 *
	 * @covers AMP_Site_Validation::does_taxonomy_support_amp()
	 */
	public function test_does_taxonomy_support_amp() {
		$custom_taxonomy = 'foo_custom_taxonomy';
		register_taxonomy( $custom_taxonomy, 'post' );
		$taxonomies_to_test = array( $custom_taxonomy, 'category', 'post_tag' );

		// When no template is unchecked in the 'AMP Settings' UI, these should be supported.
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_Site_Validation::does_taxonomy_support_amp( $taxonomy ) );
		}

		// When the user has not checked the boxes for 'Categories' and 'Tags,' this should be false.
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		AMP_Options_Manager::update_option( 'supported_templates', array( 'is_author' ) );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertFalse( AMP_Site_Validation::does_taxonomy_support_amp( $taxonomy ) );
		}

		// When $force_crawl_all_urls is true, all taxonomies should be supported.
		AMP_Site_Validation::$force_crawl_all_urls = true;
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_Site_Validation::does_taxonomy_support_amp( $taxonomy ) );
		}
		AMP_Site_Validation::$force_crawl_all_urls = false;

		// When the user has the 'all_templates_supported' box, this should always be true.
		AMP_Options_Manager::update_option( 'all_templates_supported', true );
		foreach ( $taxonomies_to_test as $taxonomy ) {
			$this->assertTrue( AMP_Site_Validation::does_taxonomy_support_amp( $taxonomy ) );
		}
		AMP_Options_Manager::update_option( 'all_templates_supported', false );

		/**
		 * If the user passed allowed conditionals to the WP-CLI command like wp amp validate-site --include=is_category,is_tag
		 * these should be supported taxonomies.
		 */
		AMP_Site_Validation::$include_conditionals = array( 'is_category', 'is_tag' );
		$this->assertTrue( AMP_Site_Validation::does_taxonomy_support_amp( 'category' ) );
		$this->assertTrue( AMP_Site_Validation::does_taxonomy_support_amp( 'tag' ) );
	}

	/**
	 * Test get_posts_by_type.
	 *
	 * @covers AMP_Site_Validation::get_posts_by_type()
	 */
	public function test_get_posts_by_type() {
		$number_posts_each_post_type = 20;
		$post_types                  = get_post_types( array( 'public' => true ), 'names' );

		/**
		 * Exclude attachment permalinks from the test.
		 * They have a default status of 'inherit,' to depend on the status of their parent post.
		 */
		unset( $post_types['attachment'] );

		foreach ( $post_types as $post_type ) {
			// Start the expected posts with the existing post.
			$query          = new WP_Query( array(
				'fields'    => 'ids',
				'post_type' => $post_type,
			) );
			$expected_posts = $query->posts;

			for ( $i = 0; $i < $number_posts_each_post_type; $i++ ) {
				$expected_posts[] = $this->factory()->post->create( array(
					'post_type' => $post_type,
				) );
			}

			$actual_posts = AMP_Site_Validation::get_posts_by_type( $post_type );
			$this->assertEquals( $expected_posts, array_values( $actual_posts ) );

			// Test with the 2 optional arguments for AMP_Site_Validation::get_posts_by_type().
			$number_of_posts = $number_posts_each_post_type / 2;
			$actual_posts    = AMP_Site_Validation::get_posts_by_type( $post_type, $number_of_posts );
			$this->assertEquals( array_slice( $expected_posts, $number_of_posts ), $actual_posts );
		}
	}

	/**
	 * Test get_taxonomy_links.
	 *
	 * @covers AMP_Site_Validation::get_taxonomy_links()
	 */
	public function test_get_taxonomy_links() {
		$number_links_each_taxonomy = 20;
		$taxonomies                 = get_taxonomies( array(
			'public' => true,
		) );

		foreach ( $taxonomies as $taxonomy ) {
			// Begin the expected links with the term links that already exist.
			$expected_links             = array_map( 'get_term_link', get_terms( array( 'taxonomy' => $taxonomy ) ) );
			$terms_for_current_taxonomy = array();
			for ( $i = 0; $i < $number_links_each_taxonomy; $i++ ) {
				$terms_for_current_taxonomy[] = $this->factory()->term->create( array(
					'taxonomy' => $taxonomy,
				) );
			}

			// Terms need to be associated with a post in order to be returned in get_terms().
			wp_set_post_terms(
				$this->factory()->post->create(),
				$terms_for_current_taxonomy,
				$taxonomy
			);

			$expected_links  = array_merge(
				$expected_links,
				array_map( 'get_term_link', $terms_for_current_taxonomy )
			);
			$number_of_links = 100;
			$actual_links    = AMP_Site_Validation::get_taxonomy_links( $taxonomy, $number_of_links );

			// The get_terms() call in get_taxonomy_links() returns an array with a first index of 1, so correct for that with array_values().
			$this->assertEquals( $expected_links, array_values( $actual_links ) );
			$this->assertLessThan( $number_of_links, count( $actual_links ) );

			$number_of_links           = 5;
			$offset                    = 10;
			$actual_links_using_offset = AMP_Site_Validation::get_taxonomy_links( $taxonomy, $number_of_links, $offset );
			$this->assertEquals( array_slice( $expected_links, $offset, $number_of_links ), array_values( $actual_links_using_offset ) );
			$this->assertEquals( $number_of_links, count( $actual_links_using_offset ) );
		}
	}

	/**
	 * Test validate_entire_site_urls.
	 *
	 * @covers AMP_Site_Validation::validate_entire_site_urls()
	 */
	public function test_validate_entire_site_urls() {
		$number_of_posts = 20;
		$number_of_terms = 30;
		$posts           = array();
		$post_permalinks = array();
		$terms           = array();

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$post_id           = $this->factory()->post->create();
			$posts[]           = $post_id;
			$post_permalinks[] = get_permalink( $post_id );
		}
		AMP_Site_Validation::validate_entire_site_urls();

		// All of the posts created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $post_permalinks, self::get_validated_urls() ) );

		for ( $i = 0; $i < $number_of_terms; $i++ ) {
			$terms[] = $this->factory()->category->create();
		}

		// Terms need to be associated with a post in order to be returned in get_terms().
		wp_set_post_terms( $posts[0], $terms, 'category' );
		AMP_Site_Validation::validate_entire_site_urls();
		$expected_validated_urls = array_map( 'get_term_link', $terms );
		$actual_validated_urls   = self::get_validated_urls();

		// All of the terms created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $expected_validated_urls, $actual_validated_urls ) );
		$this->assertTrue( in_array( home_url( '/' ), self::get_validated_urls(), true ) );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers AMP_Site_Validation::validate_urls()
	 */
	public function test_validate_urls() {
		$single_post_permalink = get_permalink( $this->factory()->post->create() );
		AMP_Site_Validation::validate_urls( array( $single_post_permalink ) );
		$this->assertTrue( in_array( $single_post_permalink, self::get_validated_urls(), true ) );

		$number_of_posts = 30;
		$post_permalinks = array();

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$post_permalinks[] = get_permalink( $this->factory()->post->create() );
		}
		AMP_Site_Validation::validate_urls( $post_permalinks );

		// All of the posts created should be present in the validated URLs.
		$this->assertEmpty( array_diff( $post_permalinks, self::get_validated_urls() ) );
	}

	/**
	 * Gets the initial count of URLs on the site.
	 *
	 * @return int The initial count of URLs.
	 */
	public function get_inital_url_count() {
		$total_count  = 'posts' === get_option( 'show_on_front' ) ? 1 : 0;
		$post_query   = new WP_Query( array( 'post_type' => get_post_types( array( 'public' => true ), 'names' ) ) );
		$total_count += $post_query->found_posts;

		$term_query = new WP_Term_Query( array(
			'taxonomy' => get_taxonomies( array( 'public' => true ) ),
			'fields'   => 'ids',
		) );

		$total_count += count( $term_query->terms );
		return $total_count;
	}

	/**
	 * Gets all of the validated URLs.
	 *
	 * @return string[] $urls The validated URLs.
	 */
	public function get_validated_urls() {
		$query = new WP_Query( array(
			'post_type'      => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			'posts_per_page' => 100,
			'fields'         => 'ids',
		) );

		return array_map(
			function( $post ) {
				return remove_query_arg( 'amp', AMP_Invalid_URL_Post_Type::get_url_from_post( $post ) );
			},
			$query->posts
		);
	}

	/**
	 * Adds the AMP_VALIDATION_RESULTS: comment to the <html> body.
	 *
	 * @return array The response, with a comment in the body.
	 */
	public function add_comment() {
		$mock_validation_results = array(
			array(
				'error'     => array(
					'code' => 'foo',
				),
				'sanitized' => false,
			),
		);

		return array(
			'body'     => sprintf(
				'<html amp><head></head><body></body><!--%s--></html>',
				'AMP_VALIDATION_RESULTS:' . wp_json_encode( $mock_validation_results )
			),
			'response' => array(
				'code'    => 200,
				'message' => 'ok',
			),
		);
	}
}
