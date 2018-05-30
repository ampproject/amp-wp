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
					'post_type'   => $post_type,
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
}
