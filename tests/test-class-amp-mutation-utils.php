<?php
/**
 * Tests for AMP_Mutation_Utils class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Mutation_Utils class.
 *
 * @since 0.7
 */
class Test_AMP_Mutation_Utils extends \WP_UnitTestCase {

	/**
	 * An instance of DOMElement to test.
	 *
	 * @var DOMDocument
	 */
	public $node;

	/**
	 * The expected REST API route.
	 *
	 * @var string
	 */
	public $expected_route = '/amp-wp/v1/validate';

	/**
	 * A tag that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_tag = '<script async src="https://example.com/script"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

	/**
	 * A valid image that sanitizers should not alter.
	 *
	 * @var string
	 */
	public $valid_amp_img = '<amp-img id="img-123" media="(min-width: 600x)" src="/assets/example.jpg" width=200 height=500 layout="responsive"></amp-img>';

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
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		AMP_Mutation_Utils::reset_removed();
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

	/**
	 * Test process_markup.
	 *
	 * @see AMP_Mutation_Utils::process_markup()
	 */
	public function test_process_markup() {

		AMP_Mutation_Utils::process_markup( $this->valid_amp_img );
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_attributes );

		AMP_Mutation_Utils::reset_removed();
		$video = '<video src="https://example.com/video">';
		AMP_Mutation_Utils::process_markup( $this->valid_amp_img );
		// This isn't valid AMP, but the sanitizer should convert it to an <amp-video>, without stripping anything.
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_attributes );

		AMP_Mutation_Utils::reset_removed();

		AMP_Mutation_Utils::process_markup( $this->disallowed_tag );
		$removed_node = reset( AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( 'script', $removed_node->nodeName );
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_attributes );

		AMP_Mutation_Utils::reset_removed();
		$disallowed_style = '<div style="display:none"></div>';
		AMP_Mutation_Utils::process_markup( $disallowed_style );
		$removed_attribute           = reset( AMP_Mutation_Utils::$removed_attributes );
		$expected_removed_attributes = array(
			'div' => 'style',
		);
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( $expected_removed_attributes, $removed_attribute );

		AMP_Mutation_Utils::reset_removed();
		$invalid_video = '<video width="200" height="100"></video>';
		AMP_Mutation_Utils::process_markup( $invalid_video );
		$removed_node = reset( AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( 'video', $removed_node->nodeName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar.
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_attributes );
	}

	/**
	 * Test amp_rest_validation.
	 *
	 * @see AMP_Mutation_Utils::amp_rest_validation()
	 */
	public function test_amp_rest_validation() {
		$routes  = rest_get_server()->get_routes();
		$route   = $routes[ $this->expected_route ][0];
		$methods = array(
			'POST' => true,
		);
		$args    = array(
			'markup' => array(
				'validate_callback' => array( 'AMP_Mutation_Utils', 'validate_arg' ),
			),
		);

		$this->assertEquals( $args, $route['args'] );
		$this->assertEquals( array( 'AMP_Mutation_Utils', 'validate_markup' ), $route['callback'] );
		$this->assertEquals( $methods, $route['methods'] );
		$this->assertEquals( array( 'AMP_Mutation_Utils', 'permission' ), $route['permission_callback'] );
	}

	/**
	 * Test permission.
	 *
	 * @see AMP_Mutation_Utils::permission()
	 */
	public function test_permission() {
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'subscriber',
		) ) );
		$this->assertFalse( AMP_Mutation_Utils::permission() );

		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );
		$this->assertTrue( AMP_Mutation_Utils::permission() );
	}

	/**
	 * Test validate_markup.
	 *
	 * @see AMP_Mutation_Utils::validate_markup()
	 */
	public function test_validate_markup() {
		$request = new WP_REST_Request( 'POST', $this->expected_route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			AMP_Mutation_Utils::MARKUP_KEY => $this->disallowed_tag,
		) ) );
		$response          = AMP_Mutation_Utils::validate_markup( $request );
		$expected_response = array(
			'is_error' => true,
		);
		$this->assertEquals( $expected_response, $response );

		$request->set_body( wp_json_encode( array(
			AMP_Mutation_Utils::MARKUP_KEY => $this->valid_amp_img,
		) ) );
		$response          = AMP_Mutation_Utils::validate_markup( $request );
		$expected_response = array(
			'is_error' => false,
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test reset_removed
	 *
	 * @see AMP_Mutation_Utils::reset_removed()
	 */
	public function test_reset_removed() {
		AMP_Mutation_Utils::$removed_nodes[]    = $this->node;
		AMP_Mutation_Utils::$removed_attributes = array( 'onclick' );
		AMP_Mutation_Utils::reset_removed();

		$this->assertEquals( null, AMP_Mutation_Utils::$removed_nodes );
		$this->assertEquals( null, AMP_Mutation_Utils::$removed_attributes );
	}

	/**
	 * Test validate_arg
	 *
	 * @see AMP_Mutation_Utils::validate_arg()
	 */
	public function test_validate_arg() {
		$invalid_number = 54321;
		$invalid_array  = array( 'foo', 'bar' );
		$valid_string   = '<div class="baz"></div>';
		$this->assertFalse( AMP_Mutation_Utils::validate_arg( $invalid_number ) );
		$this->assertFalse( AMP_Mutation_Utils::validate_arg( $invalid_array ) );
		$this->assertTrue( AMP_Mutation_Utils::validate_arg( $valid_string ) );
	}

}
