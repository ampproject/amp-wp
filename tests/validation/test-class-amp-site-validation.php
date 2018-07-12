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
		AMP_Site_Validation::$site_validation_urls = array();
		parent::tearDown();
	}

	/**
	 * Test get_post_permalinks.
	 *
	 * @covers AMP_Site_Validation::get_post_permalinks()
	 */
	public function test_get_post_permalinks() {
		$number_posts_each_post_type = 20;
		$post_types                  = get_post_types( array( 'public' => true ), 'names' );

		/**
		 * Exclude attachment permalinks from the test.
		 * They have a default status of 'inherit,' to depend on the status of their parent post.
		 */
		unset( $post_types['attachment'] );
		foreach ( $post_types as $post_type ) {
			$expected_post_permalinks = array();
			for ( $i = 0; $i < $number_posts_each_post_type; $i++ ) {
				$expected_post_permalinks[] = get_permalink( $this->factory()->post->create( array(
					'post_type' => $post_type,
				) ) );
			}

			$actual_post_permalinks = AMP_Site_Validation::get_post_permalinks( $post_type );
			$this->assertEquals( $expected_post_permalinks, array_values( $actual_post_permalinks ) );

			// Test with the 2 optional arguments for AMP_Site_Validation::get_post_permalinks().
			$number_of_posts        = $number_posts_each_post_type / 2;
			$offset                 = $number_of_posts;
			$actual_post_permalinks = AMP_Site_Validation::get_post_permalinks( $post_type, $number_of_posts, $offset );
			$this->assertEquals( array_slice( $expected_post_permalinks, $offset, $number_of_posts ), array_values( $actual_post_permalinks ) );
			$this->assertCount( $number_of_posts, $actual_post_permalinks );
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

			$expected_links  = array_map( 'get_term_link', $terms_for_current_taxonomy );
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
			$post_permalinks[] = add_query_arg( 'amp', '1', get_permalink( $post_id ) );
		}
		$validated_urls = AMP_Site_Validation::validate_entire_site_urls();

		// All of the posts created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $post_permalinks, $validated_urls ) );

		for ( $i = 0; $i < $number_of_terms; $i++ ) {
			$terms[] = $this->factory()->category->create();
		}
		// Terms need to be associated with a post in order to be returned in get_terms().
		wp_set_post_terms( $posts[0], $terms, 'category' );

		$actual_validated_urls   = AMP_Site_Validation::validate_entire_site_urls();
		$expected_validated_urls = array_map(
			function( $term ) {
				return add_query_arg( 'amp', '1', get_term_link( $term ) );
			},
			$terms
		);

		// All of the terms created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $expected_validated_urls, $actual_validated_urls ) );
		$this->assertTrue( in_array( add_query_arg( 'amp', '1', home_url( '/' ) ), $actual_validated_urls, true ) );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers AMP_Site_Validation::validate_urls()
	 */
	public function test_validate_urls() {
		$single_post_permalink = get_permalink( $this->factory()->post->create() );
		AMP_Site_Validation::validate_urls( $single_post_permalink );
		$this->assertEquals( array( add_query_arg( 'amp', 1, $single_post_permalink ) ), AMP_Site_Validation::$site_validation_urls );

		AMP_Site_Validation::$site_validation_urls = array();
		$number_of_posts                           = 30;
		$post_permalinks                           = array();

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$post_permalinks[] = add_query_arg( 'amp', 1, get_permalink( $this->factory()->post->create() ) );
		}
		AMP_Site_Validation::validate_urls( $post_permalinks );
		$this->assertEquals( $post_permalinks, AMP_Site_Validation::$site_validation_urls );
	}

	/**
	 * Adds the AMP_VALIDATION_RESULTS: comment to the <html> body.
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
			'body' => sprintf(
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
