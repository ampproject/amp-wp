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
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Site_Validation';

	/**
	 * The name of the tag to test.
	 *
	 * @var string
	 */
	const TAG_NAME = 'img';

	/**
	 * A disallowed element, which should cause a validation error.
	 *
	 * @var string
	 */
	const DISALLOWED_TAG = '<script>doThis();</script>';

	/**
	 * An instance of DOMElement to test.
	 *
	 * @var DOMElement
	 */
	public $node;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 * @global $wp_registered_widgets
	 */
	public function setUp() {
		parent::setUp();
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		AMP_Validation_Manager::reset_validation_results();
	}

	/**
	 * Test get_post_permalinks.
	 *
	 * @covers AMP_Site_Validation::get_post_permalinks()
	 */
	public function test_get_post_permalinks() {
		$number_posts_each_post_type = 20;
		$post_types                  = get_post_types( array( 'public' => true ), 'names' );
		$expected_post_permalinks    = array();

		/**
		 * The tested method does not get attachment permalinks.
		 * It only searches for posts with the status 'publish.'
		 * Attachments have a default status of 'inherit,' to depend on the status of their parent post.
		 */
		unset( $post_types['attachment'] );
		foreach ( $post_types as $post_type ) {
			for ( $i = 0; $i < $number_posts_each_post_type; $i++ ) {
				$expected_post_permalinks[] = get_permalink( $this->factory()->post->create( array(
					'post_type' => $post_type,
				) ) );
			}
		}
		$number_of_posts        = count( $post_types ) * $number_posts_each_post_type;
		$actual_post_permalinks = AMP_Site_Validation::get_post_permalinks( $number_of_posts );

		/*
		 * The factory() method above creates posts so quickly that the WP_Query() default argument of 'orderby' => 'date'
		 * doesn't return them in the exact order they were created.
		 * So this simply ensures all of the created $post_ids are present in the return value of the tested method.
		 */
		$this->assertEquals( 0, count( array_diff( $expected_post_permalinks, $actual_post_permalinks ) ) );
		$this->assertEquals( count( $expected_post_permalinks ), count( $actual_post_permalinks ) );
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

		unset( $taxonomies['post_format'] );
		$all_terms = array();

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
			$all_terms = array_merge( $all_terms, $terms_for_current_taxonomy );
		}

		$expected_links  = array_map( 'get_term_link', $all_terms );
		$number_of_links = 100;
		$actual_links    = AMP_Site_Validation::get_taxonomy_links( $number_of_links );

		/*
		 * Test that all of the $expected_links are present.
		 * There is already one term present before this test method adds any,
		 * so that can also appear in the returned $actual_links.
		 */
		$this->assertEquals( 0, count( array_diff( $expected_links, $actual_links ) ) );
		$this->assertLessThan( $number_of_links, count( $actual_links ) );
	}
}
