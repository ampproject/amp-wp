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
		$GLOBALS['post'] = self::factory()->post->create_and_get();
		$this->dom       = new DOMDocument();
		$this->instance  = new AMP_Comments_Sanitizer( $this->dom );
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize_incorrect_action() {
		$form = $this->create_form( 'incorrect-action.php' );
		$this->instance->sanitize();
		$on = $form->getAttribute( 'on' );
		$this->assertNotContains( 'submit:AMP.setState(', $on );
		$this->assertNotContains( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_form_element_names() as $name ) {
			$this->assertNotContains( $name, $on );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize_allowed_action() {
		// Use an allowed action.
		$form = $this->create_form( '/wp-comments-post.php' );
		$this->instance->sanitize();
		$on = $form->getAttribute( 'on' );
		$this->assertContains( 'submit:AMP.setState(', $on );
		$this->assertContains( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_form_element_names() as $name ) {
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

		foreach ( $this->get_form_element_names() as $name ) {
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
	 * Test AMP_Comment_Walker::paged_walk.
	 *
	 * @covers AMP_Comments_Sanitizer::process_comment()
	 */
	public function test_process_comment() {
		$GLOBALS['post'] = self::factory()->post->create();

		$comment_objects = $this->get_comments();
		$this->create_comments_list( $comment_objects );
		$this->instance->sanitize();

		$xpath = new DOMXPath( $this->dom );
		$comments = $xpath->query('//*[starts-with(@id,\'comment-\')]' );

		foreach( $comments as $comment ) {
			/** @var DOMElement $comment */

			$comment_id = (int) str_replace( 'comment-', '', $comment->getAttribute( 'id' ) );

			$this->assertArrayHasKey( $comment_id, $comment_objects );

			$comment_object = $comment_objects[ $comment_id ];

			$this->assertEquals( strtotime( $comment_object->comment_date ), $comment->getAttribute( 'data-sort-time' ) );

			$update_time = $comment_object->comment_date;

			$children = $comment_object->get_children(
				array(
					'hierarchical' => 'flat',
					'orderby'      => 'comment_date'
				)
			);

			if ( ! empty( $children ) ) {
				$update_time = $children[0]->comment_date;
			}

			$this->assertEquals( strtotime( $update_time ), $comment->getAttribute( 'data-update-time' ) );
		}
	}

	/**
	 * Creates a form for testing.
	 *
	 * @param string $action_value Value of the 'action' attribute.
	 * @return DOMElement $form A form element.
	 */
	public function create_form( $action_value ) {
		$form = $this->dom->createElement( 'form' );
		$this->dom->appendChild( $form );
		$form->setAttribute( 'action', $action_value );

		foreach ( $this->get_form_element_names() as $name ) {
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
	public function get_form_element_names() {
		return array(
			'comment_post_ID',
			'foo',
			'bar',
		);
	}

	public function create_comments_list( $comments = array() ) {
		ob_start();
		wp_list_comments(
			array(),
			$comments
		);

		$this->dom->loadHTML( ob_get_clean() );
	}

	/**
	 * Gets comments for tests.
	 *
	 * @return array $comments An array of WP_Comment instances.
	 */
	public function get_comments() {
		$comments = array();

		for ( $i = 0; $i < 5; $i++ ) {
			$comment = self::factory()->comment->create_and_get(
				array(
					'comment_date' => gmdate( 'Y-m-d H:i:s', time() + $i ), // Ensure each comment has a different date.
				)
			);

			$comments[ $comment->comment_ID ] = $comment;

			for ( $j = 0; $j < 3; $j++ ) {
				$child = self::factory()->comment->create_and_get(
					array(
						'comment_parent' => $comment->comment_ID,
						'comment_date'   => gmdate( 'Y-m-d H:i:s', time() + $i + $j ), // Ensure each comment has a different date.
					)
				);

				$comments[ $child->comment_ID ] = $child;
			}
		}

		return $comments;
	}
}
