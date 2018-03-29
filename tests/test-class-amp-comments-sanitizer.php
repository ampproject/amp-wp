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
	 * Instance of AMP_Comments_Sanitizer
	 *
	 * @var AMP_Comments_Sanitizer
	 */
	public $instance;

	/**
	 * Instance of DOMDocument
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
		$this->dom      = new DOMDocument();
		$this->instance = new AMP_Comments_Sanitizer( $this->dom );
		parent::setUp();
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize() {

	}

	/**
	 * Test AMP_Comments_Sanitizer::process_comment_form.
	 *
	 * @covers AMP_Comments_Sanitizer::process_comment_form()
	 */
	public function test_process_comment_form() {
		$GLOBALS['post'] = $this->factory()->post->create_and_get();
		$form            = $this->dom->createElement( 'form' );

		$names = array(
			'comment_post_ID',
			'foo',
			'bar',
		);
		foreach ( $names as $name ) {
			$element = $this->dom->createElement( 'input' );
			$element->setAttribute( 'name', $name );
			$element->setAttribute( 'value', $GLOBALS['post']->ID );
			$form->appendChild( $element );
		}

		$reflection    = new ReflectionObject( $this->instance );
		$tested_method = $reflection->getMethod( 'process_comment_form' );
		$tested_method->setAccessible( true );
		$tested_method->invoke( $this->instance, $form );
		$on = $form->getAttribute( 'on' );

		$this->assertContains( 'submit:AMP.setState(', $on );
		$this->assertContains( 'submit-error:AMP.setState(', $on );
		$this->assertContains( 'submit-success:AMP.setState(', $on );
		$this->assertContains( strval( $GLOBALS['post']->ID ), $on );
	}

}
