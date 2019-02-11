<?php
/**
 * Tests for AMP_Comment_Walker class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Comment_Walker class.
 *
 * @since 0.7
 */
class Test_AMP_Comment_Walker extends WP_UnitTestCase {

	/**
	 * The comment walker.
	 *
	 * @var AMP_Comment_Walker
	 */
	public $walker;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->walker = new AMP_Comment_Walker();
	}

	/**
	 * Test AMP_Comment_Walker::start_el.
	 *
	 * @covers AMP_Comment_Walker::start_el()
	 */
	public function test_start_el() {
		$GLOBALS['post'] = $this->factory()->post->create();
		$output          = '<div></div>';
		$base_args       = array(
			'format'      => 'baz',
			'avatar_size' => 100,
			'max_depth'   => 5,
		);
		$args            = array_merge(
			$base_args,
			array(
				'style' => 'baz',
			)
		);
		$comment         = $this->factory()->comment->create_and_get();
		$this->walker->start_el( $output, $comment, 0, $args );
		$this->assertContains( '<li data-sort-time=', $output );
		$this->assertContains( $comment->comment_ID, $output );
		$this->assertContains( strval( strtotime( $comment->comment_date ) ), $output );

		$output  = '<div></div>';
		$args    = array_merge(
			$base_args,
			array(
				'style' => 'div',
			)
		);
		$comment = $this->factory()->comment->create_and_get();
		$this->walker->start_el( $output, $comment, 0, $args );
		$this->assertContains( '<div data-sort-time=', $output );
	}

	/**
	 * Test AMP_Comment_Walker::paged_walk.
	 *
	 * @covers AMP_Comment_Walker::paged_walk()
	 */
	public function test_paged_walk() {
		$GLOBALS['post'] = $this->factory()->post->create();
		$comments        = $this->get_comments();
		$args            = array(
			'format'      => 'div',
			'style'       => 'baz',
			'avatar_size' => 100,
			'max_depth'   => 5,
		);
		$output          = $this->walker->paged_walk( $comments, 5, 1, 5, $args );

		foreach ( $comments as $comment ) {
			$this->assertContains( $comment->comment_author, $output );
			$this->assertContains( $comment->comment_content, $output );
		}
	}

	/**
	 * Test AMP_Comment_Walker::build_thread_latest_date.
	 *
	 * @covers AMP_Comment_Walker::build_thread_latest_date()
	 */
	public function test_build_thread_latest_date() {
		$comments      = $this->get_comments();
		$reflection    = new ReflectionObject( $this->walker );
		$tested_method = $reflection->getMethod( 'build_thread_latest_date' );
		$tested_method->setAccessible( true );
		$latest_time        = $tested_method->invoke( $this->walker, $comments );
		$comment_thread_age = $reflection->getProperty( 'comment_thread_age' );
		$comment_thread_age->setAccessible( true );
		$comment_thread_value = $comment_thread_age->getValue( $this->walker );

		foreach ( $comments as $comment ) {
			$this->assertEquals( strtotime( $comment->comment_date ), $comment_thread_value[ $comment->comment_ID ] );
		}

		$last_comment = end( $comments );
		$this->assertEquals( strtotime( $last_comment->comment_date ), $latest_time );
	}

	/**
	 * Gets comments for tests.
	 *
	 * @return array $comments An array of WP_Comment instances.
	 */
	public function get_comments() {
		$comments = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$comments[] = $this->factory()->comment->create_and_get(
				array(
					'comment_date' => gmdate( 'Y-m-d H:i:s', ( time() + $i ) ), // Ensure each comment has a different date.
				)
			);
		}
		return $comments;
	}

}
