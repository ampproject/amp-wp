<?php
/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\Dom\Document;

/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @since 0.7
 */
class Test_AMP_Comments_Sanitizer extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Representation of the DOM.
	 *
	 * @var Document
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
		$this->dom       = new Document();
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize_incorrect_action() {
		$instance = new AMP_Comments_Sanitizer( $this->dom );

		$form = $this->create_form( 'incorrect-action.php' );
		$instance->sanitize();
		$on = $form->getAttribute( 'on' );
		$this->assertStringNotContainsString( 'submit:AMP.setState(', $on );
		$this->assertStringNotContainsString( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_form_element_names() as $name ) {
			$this->assertStringNotContainsString( $name, $on );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize_allowed_action_xhr() {
		$form_sanitizer     = new AMP_Form_Sanitizer( $this->dom );
		$comments_sanitizer = new AMP_Comments_Sanitizer( $this->dom );

		// Use an allowed action.
		$form = $this->create_form( '/wp-comments-post.php' );
		$form_sanitizer->sanitize();
		$comments_sanitizer->sanitize();

		$on = $form->getAttribute( 'on' );
		$this->assertStringContainsString( 'submit:AMP.setState(', $on );
		$this->assertStringContainsString( 'submit-error:AMP.setState(', $on );
		foreach ( $this->get_form_element_names() as $name ) {
			$this->assertStringContainsString( $name, $on );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize() when a comments form has not been converted into an amp-form.
	 *
	 * @covers AMP_Comments_Sanitizer::sanitize()
	 */
	public function test_sanitize_native_post_form() {
		$comments_sanitizer = new AMP_Comments_Sanitizer( $this->dom );

		// Use an allowed action.
		$form = $this->create_form( '/wp-comments-post.php' );
		$comments_sanitizer->sanitize();
		$this->assertFalse( $form->hasAttribute( 'on' ) );
	}

	/**
	 * Test AMP_Comments_Sanitizer::process_comment_form.
	 *
	 * @covers AMP_Comments_Sanitizer::process_comment_form()
	 */
	public function test_process_comment_form() {
		$instance = new AMP_Comments_Sanitizer( $this->dom );

		$form = $this->create_form( '/wp-comments-post.php' );
		$this->call_private_method( $instance, 'process_comment_form', [ $form ] );

		$on        = $form->getAttribute( 'on' );
		$amp_state = $this->dom->getElementsByTagName( 'amp-state' )->item( 0 );

		$this->assertStringContainsString( 'submit:AMP.setState(', $on );
		$this->assertStringContainsString( 'submit-error:AMP.setState(', $on );
		$this->assertStringContainsString( 'submit-success:AMP.setState(', $on );
		$this->assertStringContainsString( strval( $GLOBALS['post']->ID ), $on );
		$this->assertEquals( 'script', $amp_state->firstChild->nodeName );

		foreach ( $this->get_form_element_names() as $name ) {
			$this->assertStringContainsString( $name, $on );
			$this->assertStringContainsString( $name, $amp_state->nodeValue );
		}
		foreach ( $form->getElementsByTagName( 'input' ) as $input ) {
			/**
			 * Input.
			 *
			 * @var DOMElement $input
			 */
			$on = $input->getAttribute( 'on' );
			$this->assertStringContainsString( 'change:AMP.setState(', $on );
			$this->assertStringContainsString( strval( $GLOBALS['post']->ID ), $on );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::add_amp_live_list_comment_attributes.
	 *
	 * @covers AMP_Comments_Sanitizer::add_amp_live_list_comment_attributes()
	 */
	public function test_add_amp_live_list_comment_attributes() {
		$instance = new AMP_Comments_Sanitizer(
			$this->dom,
			[
				'comments_live_list' => true,
			]
		);

		$GLOBALS['post'] = self::factory()->post->create();

		$comment_objects = $this->get_comments();
		$this->create_comments_list( $comment_objects );
		$instance->sanitize();

		$comments = $this->dom->xpath->query( '//*[ starts-with( @id, "comment-" ) ]' );

		foreach ( $comments as $comment ) {
			/**
			 * Comment element.
			 *
			 * @var DOMElement $comment
			 */

			$comment_id = (int) str_replace( 'comment-', '', $comment->getAttribute( 'id' ) );

			$this->assertArrayHasKey( $comment_id, $comment_objects );

			$comment_object = $comment_objects[ $comment_id ];

			if ( $comment_object->comment_parent ) {
				$this->assertFalse( $comment->hasAttribute( 'data-sort-time' ) );
				$this->assertFalse( $comment->hasAttribute( 'data-update-time' ) );
			} else {
				$this->assertEquals( strtotime( $comment_object->comment_date ), $comment->getAttribute( 'data-sort-time' ) );

				$update_time = strtotime( $comment_object->comment_date );
				$children    = $comment_object->get_children(
					[
						'format'       => 'flat',
						'hierarchical' => 'flat',
						'orderby'      => 'none',
					]
				);
				foreach ( $children as $child_comment ) {
					$update_time = max( strtotime( $child_comment->comment_date ), $update_time );
				}

				$this->assertEquals( $update_time, $comment->getAttribute( 'data-update-time' ) );
			}
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
		$form->setAttribute( 'method', 'post' );

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
		return [
			'comment_post_ID',
			'foo',
			'bar',
		];
	}

	/**
	 * Populate the DOM with comments list.
	 *
	 * @param WP_Comment[] $comments Comments.
	 */
	public function create_comments_list( $comments = [] ) {
		ob_start();

		echo '<amp-live-list><ol items>';
		wp_list_comments(
			[],
			$comments
		);
		echo '</ol></amp-live-list>';
		$html = ob_get_clean();

		@$this->dom->loadHTML( $html ); // phpcs:ignore
	}

	/**
	 * Gets comments for tests.
	 *
	 * @return WP_Comment[] $comments An array of WP_Comment instances.
	 */
	public function get_comments() {
		$comments = [];

		for ( $i = 0; $i < 5; $i++ ) {
			$comment = self::factory()->comment->create_and_get(
				[
					'comment_date' => gmdate( 'Y-m-d H:i:s', time() + $i ), // Ensure each comment has a different date.
				]
			);

			$comments[ $comment->comment_ID ] = $comment;

			for ( $j = 0; $j < 3; $j++ ) {
				$child = self::factory()->comment->create_and_get(
					[
						'comment_parent' => $comment->comment_ID,
						'comment_date'   => gmdate( 'Y-m-d H:i:s', time() + $i + $j ), // Ensure each comment has a different date.
					]
				);

				$comments[ $child->comment_ID ] = $child;
			}
		}

		return $comments;
	}
}
