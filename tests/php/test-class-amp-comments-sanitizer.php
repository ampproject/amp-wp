<?php
/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @package AMP
 */

use AmpProject\Amp;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Tag;

/**
 * Tests for AMP_Comments_Sanitizer class.
 *
 * @since 0.7
 *
 * @coversDefaultClass \AMP_Comments_Sanitizer
 */
class Test_AMP_Comments_Sanitizer extends TestCase {

	use PrivateAccess;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$GLOBALS['post'] = self::factory()->post->create_and_get();

		$GLOBALS['wp_scripts'] = null;
	}

	public function tearDown() {
		$GLOBALS['wp_scripts'] = null;

		parent::tearDown();
	}

	/**
	 * Test AMP_Comments_Sanitizer::sanitize.
	 *
	 * @covers ::sanitize()
	 */
	public function test_sanitize_incorrect_action() {
		$dom = Document::fromHtmlFragment(
			'<form action="https://example.com/" method="post"></form>'
		);
		update_option( 'thread_comments', '1' );
		$sanitizer = new AMP_Comments_Sanitizer( $dom );
		$sanitizer->sanitize();
		$this->assertFalse( $dom->getElementsByTagName( Tag::FORM )->item( 0 )->hasAttribute( Attribute::ON ) );
	}

	/**
	 * @covers ::sanitize()
	 * @covers ::ampify_threaded_comments()
	 */
	public function test_ampify_threaded_comments_without_threading() {
		update_option( 'thread_comments', '' );
		setup_postdata( get_the_ID() );
		$dom = $this->get_document_with_comments( get_the_ID() );
		$this->assertNull( $dom->getElementById( 'comment-reply-js' ) );
		$sanitizer = new AMP_Comments_Sanitizer( $dom );

		$sanitizer->sanitize();
		$commentform = $dom->getElementById( 'commentform' );
		$this->assertInstanceOf( Element::class, $commentform );
		$this->assertFalse( $commentform->hasAttribute( Attribute::ON ) );
		$this->assertNull( $dom->getElementById( 'ampCommentThreading' ) );
		$this->assertNull( $dom->getElementById( 'comment-reply-js' ) );
	}

	/** @return array */
	public function get_data_to_test_ampify_threaded_comments_always_and_conditionally() {
		return [
			'never'             => [
				'ampify_comment_threading'        => 'never',
				'comments_form_has_action_xhr'    => false,
				'expect_ampify_comment_threading' => false,
			],
			'always'            => [
				'ampify_comment_threading'        => 'always',
				'comments_form_has_action_xhr'    => true,
				'expect_ampify_comment_threading' => true,
			],
			'conditionally_no'  => [
				'ampify_comment_threading'        => 'conditionally',
				'comments_form_has_action_xhr'    => false,
				'expect_ampify_comment_threading' => false,
			],
			'conditionally_yes' => [
				'ampify_comment_threading'        => 'conditionally',
				'comments_form_has_action_xhr'    => true,
				'expect_ampify_comment_threading' => true,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_ampify_threaded_comments_always_and_conditionally
	 *
	 * @covers ::sanitize()
	 * @covers ::ampify_threaded_comments()
	 * @covers ::prepare_native_comment_reply()
	 */
	public function test_ampify_threaded_comments( $ampify_comment_threading, $comments_form_has_action_xhr, $expect_ampify_comment_threading ) {
		if ( version_compare( get_bloginfo( 'version' ), '5.2', '<' ) ) {
			$this->markTestSkipped( 'Skipping because the script ID attribute was added in WP 5.2.' );
		}

		update_option( 'thread_comments', '1' );
		setup_postdata( get_the_ID() );
		$dom = $this->get_document_with_comments( get_the_ID() );

		$comment_form = $dom->getElementById( 'commentform' );
		$this->assertInstanceOf( Element::class, $comment_form );

		if ( $comments_form_has_action_xhr ) {
			$comment_form->setAttribute(
				Attribute::ACTION_XHR,
				$comment_form->getAttribute( Attribute::ACTION )
			);
			$comment_form->removeAttribute( Attribute::ACTION );
		}

		$comments_sanitizer = new AMP_Comments_Sanitizer(
			$dom,
			[
				'ampify_comment_threading' => $ampify_comment_threading,
			]
		);
		$style_sanitizer    = new AMP_Style_Sanitizer( $dom );

		/** @var AMP_Base_Sanitizer[] $sanitizers */
		$sanitizers = [
			AMP_Comments_Sanitizer::class => $comments_sanitizer,
			AMP_Style_Sanitizer::class    => $style_sanitizer,
		];

		// Ensure initial state.
		$this->assertFalse( $comment_form->hasAttribute( Attribute::ON ) );
		$script = $dom->getElementById( 'comment-reply-js' );
		$this->assertInstanceOf( Element::class, $script );
		$this->assertInstanceOf( Element::class, $script->parentNode );
		$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );

		// Sanitize.
		foreach ( $sanitizers as $sanitizer ) {
			$sanitizer->init( $sanitizers );
		}
		$comments_sanitizer->sanitize();

		if ( ! $expect_ampify_comment_threading ) {
			$this->assertEquals(
				$comments_form_has_action_xhr,
				$comment_form->hasAttribute( Attribute::ON )
			);
			$this->assertInstanceOf( Element::class, $script->parentNode );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
			$this->assertTrue( $script->hasAttribute( Attribute::DEFER ) );
			$this->assertNull( $dom->getElementById( 'ampCommentThreading' ) );

			$this->assertFalse( $style_sanitizer->get_arg( 'transform_important_qualifiers' ) );
		} else {
			$this->assertNull( $script->parentNode );

			$json_script = $dom->xpath->query( '//amp-state[ @id = "ampCommentThreading" ]/script[ @type = "application/json" ]' )->item( 0 );
			$this->assertInstanceOf( Element::class, $json_script );
			$this->assertEquals(
				[
					'replyTo'       => '',
					'commentParent' => '0',
				],
				json_decode( $json_script->textContent, true )
			);

			$comment_parent_input = $dom->getElementById( 'comment_parent' );
			$this->assertInstanceOf( Element::class, $comment_parent_input );
			$this->assertEquals( '0', $comment_parent_input->getAttribute( Attribute::VALUE ) );
			$this->assertEquals(
				'ampCommentThreading.commentParent',
				$comment_parent_input->getAttribute( Amp::BIND_DATA_ATTR_PREFIX . 'value' )
			);

			$this->assertEquals(
				$comment_form->getAttribute( Attribute::ON ),
				'submit-success:commentform.clear,AMP.setState({ampCommentThreading: {"replyTo":"","commentParent":"0"}})'
			);

			$reply_heading_element = $dom->getElementById( 'reply-title' );
			$this->assertInstanceOf( Element::class, $reply_heading_element );
			$span = $reply_heading_element->firstChild;
			$this->assertInstanceOf( Element::class, $span );
			$this->assertTrue( $span->hasAttribute( Amp::BIND_DATA_ATTR_PREFIX . 'text' ) );

			$comment_reply_links = $dom->xpath->query( '//a[ @data-commentid and @data-postid and @data-replyto and @data-respondelement and contains( @class, "comment-reply-link" ) ]' );
			$this->assertGreaterThan( 0, $comment_reply_links->length );
			foreach ( $comment_reply_links as $comment_reply_link ) {
				/** @var Element $comment_reply_link */
				$this->assertStringStartsWith( '#', $comment_reply_link->getAttribute( Attribute::HREF ) );
				$this->assertStringContainsString( 'comment.focus', $comment_reply_link->getAttribute( Attribute::ON ) );
				$this->assertStringContainsString( 'AMP.setState', $comment_reply_link->getAttribute( Attribute::ON ) );
			}

			$cancel_comment_reply_link = $dom->getElementById( 'cancel-comment-reply-link' );
			$this->assertInstanceOf( Element::class, $cancel_comment_reply_link );

			$this->assertFalse( $cancel_comment_reply_link->hasAttribute( Attribute::STYLE ) );
			$this->assertTrue( $cancel_comment_reply_link->hasAttribute( Attribute::HIDDEN ) );
			$this->assertTrue( $cancel_comment_reply_link->hasAttribute( Amp::BIND_DATA_ATTR_PREFIX . Attribute::HIDDEN ) );
			$this->assertStringContainsString(
				'tap:AMP.setState',
				$cancel_comment_reply_link->getAttribute( Attribute::ON )
			);

			$this->assertTrue( $style_sanitizer->get_arg( 'transform_important_qualifiers' ) );
		}
	}

	/**
	 * Test AMP_Comments_Sanitizer::add_amp_live_list_comment_attributes.
	 *
	 * @covers ::add_amp_live_list_comment_attributes()
	 */
	public function test_add_amp_live_list_comment_attributes() {
		$dom       = $this->get_document_with_comments( get_the_ID(), true );
		$sanitizer = new AMP_Comments_Sanitizer(
			$dom,
			[
				'comments_live_list' => true,
			]
		);

		$sanitizer->sanitize();

		$this->assertEquals( 1, $dom->getElementsByTagName( Extension::LIVE_LIST )->length );

		$comments_elements = $dom->xpath->query( '//li[ starts-with( @id, "comment-" ) ]' );
		$this->assertGreaterThan( 0, $comments_elements->length );

		foreach ( $comments_elements as $comment_element ) {
			/** @var Element $comment_element */

			$comment_id = (int) str_replace( 'comment-', '', $comment_element->getAttribute( 'id' ) );

			$comment_object = get_comment( $comment_id );
			$this->assertInstanceOf( WP_Comment::class, $comment_object );

			if ( $comment_object->comment_parent ) {
				$this->assertFalse( $comment_element->hasAttribute( 'data-sort-time' ) );
				$this->assertFalse( $comment_element->hasAttribute( 'data-update-time' ) );
			} else {
				$this->assertTrue( $comment_element->hasAttribute( 'data-sort-time' ) );
				$this->assertTrue( $comment_element->hasAttribute( 'data-update-time' ) );

				$this->assertEquals( strtotime( $comment_object->comment_date ), $comment_element->getAttribute( 'data-sort-time' ) );

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

				$this->assertEquals( $update_time, $comment_element->getAttribute( 'data-update-time' ) );
			}
		}
	}

	/**
	 * Get document with comments and comment form.
	 *
	 * @param int $post_id        Post ID.
	 * @param bool $add_live_list Add live list.
	 * @return Document
	 */
	protected function get_document_with_comments( $post_id, $add_live_list = false ) {
		/** @var WP_Comment[] $comments */
		$parent_comments = self::factory()->comment->create_post_comments( $post_id, 2 );
		$reply_comments  = self::factory()->comment->create_post_comments(
			$post_id,
			2,
			[ 'comment_parent' => $parent_comments[0] ]
		);
		$comments        = array_merge( $parent_comments, $reply_comments );
		setup_postdata( $post_id );

		ob_start();
		if ( $add_live_list ) {
			echo '<amp-live-list id="live-comments">';
		}
		printf( '<ol class="commentlist" %s>', $add_live_list ? 'items' : '' );
		wp_list_comments( [], array_map( 'get_comment', $comments ) );
		echo '</ol>';
		if ( $add_live_list ) {
			echo '</amp-live-list>';
		}
		comment_form();
		if ( get_option( 'thread_comments' ) ) {
			wp_print_scripts( [ 'comment-reply' ] );
		}
		$html = ob_get_clean();

		return Document::fromHtmlFragment( $html );
	}
}
