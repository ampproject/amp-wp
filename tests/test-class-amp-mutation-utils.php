<?php
/**
 * Tests for AMP_Mutation_Utils class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Mutation_Utils class.
 *
 * @package AMP
 */
class Test_AMP_Mutation_Utils extends \WP_UnitTestCase {

	/**
	 * An instance of DOMElement to test.
	 *
	 * @var DOMDocument
	 */
	public $node;

	/**
	 * The name of the tag to test.
	 *
	 * @var string
	 */
	const TAG_NAME = 'img';

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$dom_document                           = new DOMDocument( '1.0', 'utf-8' );
		$this->node                             = $dom_document->createElement( self::TAG_NAME );
		AMP_Mutation_Utils::$removed_nodes      = null;
		AMP_Mutation_Utils::$removed_attributes = null;
	}

	/**
	 * Test track_removed.
	 *
	 * @see AMP_Mutation_Utils::track_removed()
	 */
	public function test_track_removed() {
		$attr_name             = 'invalid-attr';
		$expected_removed_attr = array(
			array(
				$this->node->nodeName => $attr_name, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			),
		);
		$this->assertEmpty( AMP_Mutation_Utils::$removed_attributes );
		$this->assertEmpty( AMP_Mutation_Utils::$removed_nodes );
		AMP_Mutation_Utils::track_removed( $this->node, AMP_Mutation_Utils::ATTRIBUTE_REMOVED, $attr_name );
		$this->assertEquals( $expected_removed_attr, AMP_Mutation_Utils::$removed_attributes );
		AMP_Mutation_Utils::track_removed( $this->node, AMP_Mutation_Utils::NODE_REMOVED );
		$this->assertEquals( array( $this->node ), AMP_Mutation_Utils::$removed_nodes );
	}

	/**
	 * Test was_node_removed.
	 *
	 * @see AMP_Mutation_Utils::was_node_removed()
	 */
	public function test_was_node_removed() {
		$attr_name = 'invalid-attr';
		$this->assertFalse( AMP_Mutation_Utils::was_node_removed() );
		AMP_Mutation_Utils::track_removed( $this->node, AMP_Mutation_Utils::NODE_REMOVED );
		$this->assertTrue( AMP_Mutation_Utils::was_node_removed() );
	}

}
