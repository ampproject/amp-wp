<?php
/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @since 0.7
 */
class Test_AMP_Comments_Sanitizer extends WP_UnitTestCase {

	/**
	 * The tested instance.
	 *
	 * @var AMP_Comments_Sanitizer
	 */
	public $instance;

	/**
	 * Representation of the DOM.
	 *
	 * @var DOMDocument
	 */
	public $dom;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$GLOBALS['post'] = $this->factory()->post->create_and_get();
		$this->dom       = new DOMDocument();
		$this->instance  = new AMP_Comments_Sanitizer( $this->dom );
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize() {
		$form = $this->create_form( 'incorrect-action.php' );
		$this->instance->sanitize();
		$on = $form->getAttribute( 'on' );
		$this->assertNotContains( 'submit:AMP.setState(', $on );
		$this->assertNotContains( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_element_names() as $name ) {
			$this->assertNotContains( $name, $on );
		}

		// Use an allowed action.
		$form = $this->create_form( '/wp-comments-post.php' );
		$this->instance->sanitize();
		$on = $form->getAttribute( 'on' );
		$this->assertContains( 'submit:AMP.setState(', $on );
		$this->assertContains( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_element_names() as $name ) {
			$this->assertContains( $name, $on );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::process_comment_form.
	 *
	 * @covers AMP_Comments_Sanitizer::process_comment_form()
	 */
	public function test_process_comment_form() {
		$form          = $this->create_form( '/wp-comments-post.php' );
		$reflection    = new ReflectionObject( $this->instance );
		$tested_method = $reflection->getMethod( 'process_comment_form' );
		$tested_method->setAccessible( true );
		$tested_method->invoke( $this->instance, $form );
		$on        = $form->getAttribute( 'on' );
		$amp_state = $this->dom->getElementsByTagName( 'amp-state' )->item( 0 );

		$this->assertContains( 'submit:AMP.setState(', $on );
		$this->assertContains( 'submit-error:AMP.setState(', $on );
		$this->assertContains( 'submit-success:AMP.setState(', $on );
		$this->assertContains( strval( $GLOBALS['post']->ID ), $on );
		$this->assertEquals( 'script', $amp_state->firstChild->nodeName );

		foreach ( $this->get_element_names() as $name ) {
			$this->assertContains( $name, $on );
			$this->assertContains( $name, $amp_state->nodeValue );
		}
		foreach ( $form->getElementsByTagName( 'input' ) as $input ) {
			$on = $input->getAttribute( 'on' );
			$this->assertContains( 'change:AMP.setState(', $on );
			$this->assertContains( strval( $GLOBALS['post']->ID ), $on );
		}
	}

	/**
	 * Creates a form for testing.
	 *
	 * @param string $action_value Value of the 'action' attribute.
	 * @return DomElement $form A form element.
	 */
	public function create_form( $action_value ) {
		$form = $this->dom->createElement( 'form' );
		$this->dom->appendChild( $form );
		$form->setAttribute( 'action', $action_value );

		foreach ( $this->get_element_names() as $name ) {
			$element = $this->dom->createElement( 'input' );
			$element->setAttribute( 'name', $name );
			$element->setAttribute( 'value', $GLOBALS['post']->ID );
			$form->appendChild( $element );
		}
		return $form;
	}

	/**
	 * Gets the element names to add to the <form>.
	 *
	 * @return array An array of strings to add to the <form>.
	 */
	public function get_element_names() {
		return array(
			'comment_post_ID',
			'foo',
			'bar',
		);
	}

}
