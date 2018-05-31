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
}
