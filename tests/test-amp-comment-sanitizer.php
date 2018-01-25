<?php

/**
 * Class AMP_Form_Sanitizer_Test
 *
 * @group amp-comments
 * @group amp-comment
 */
class AMP_Comment_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'no_comments'                            => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),
			'no_amp_list'                            => array(
				'<div><p><comment-template></comment-template></p></div>',
				'<div><p><comment-template></comment-template></p></div>',
			),
			'no_amp_list_template'                   => array(
				'<div><p><template></template><comment-template></comment-template></p></div>',
				'<div><p><template></template><comment-template></comment-template></p></div>',
			),
			'amp_list_no_template'                   => array(
				'<div><p><amp-list></amp-list><comment-template></comment-template></p></div>',
				'<div><p><amp-list></amp-list><comment-template></comment-template></p></div>',
			),
			'amp_list_template_moved_empty_template' => array(
				'<div><p><amp-list><template></template></amp-list><comment-template>{{#comment}}<div>comment_template</div>{{/comment}}</comment-template></p></div>',
				'<div><amp-list><template><p><comment-template>{{#comment}}<div>comment_template</div>{{/comment}}</comment-template></p></template></amp-list></div>',
			),
			'url_template_strings'                   => array(
				'<div><p><amp-list><template></template></amp-list><comment-template><div><a href="https://comment_author_url"></a></div></comment-template></p></div>',
				'<div><amp-list><template><p><comment-template><div><a href="{{comment_author_url}}"></a></div></comment-template></p></template></amp-list></div>',
			),
		);
	}

	/**
	 * Test html conversion.
	 *
	 * @param string $source The source HTML.
	 * @param string $expected The expected HTML after conversion.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Comments_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test amp-list template build.
	 */
	public function test_template_build() {

		$this->navigate_to_post();
		$template = get_echo( 'comments_template' );

		$amp_list = preg_match( '/amp-list/', $template );
		$this->assertEquals( 1, $amp_list );

		$amp_template = preg_match( '/<template type="amp-mustache">/', $template );
		$this->assertEquals( 1, $amp_template );

		$comment_template = preg_match( '/comment-template/', $template );
		$this->assertEquals( 1, $comment_template );

	}

	/**
	 * Test the scripts are added.
	 */
	public function test_scripts() {
		$this->navigate_to_post();
		$template = '<div class="comments">' . get_echo( 'comments_template' ) . '</div>';
		$expected = array(
			'amp-mustache' => 'https://cdn.ampproject.org/v0/amp-mustache-latest.js',
			'amp-list'     => 'https://cdn.ampproject.org/v0/amp-list-latest.js',
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $template );

		$comment_sanitizer = new AMP_Comments_Sanitizer( $dom );
		$comment_sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );

	}

	/**
	 * Helper to navigate to a test post.
	 */
	public function navigate_to_post() {
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		$this->go_to( get_permalink( $post_id ) );
	}
}
