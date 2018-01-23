<?php
/**
 * Tests for Theme Support.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for Theme Support.
 *
 * @covers AMP_Theme_Support
 */
class Test_AMP_Theme_Support extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_theme_support( 'amp' );
	}

	/**
	 * Test is_paired_available.
	 *
	 * @covers AMP_Theme_Support::is_paired_available()
	 */
	public function test_is_paired_available() {

		// Establish initial state.
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		remove_theme_support( 'amp' );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );

		// Paired support is not available if theme support is not present or canonical.
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );

		// Paired mode is available once template_dir is supplied.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		// Paired mode not available when post does not support AMP.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		$this->assertTrue( is_singular() );
		query_posts( array( 's' => 'test' ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );
		remove_filter( 'amp_skip_post', '__return_true' );

		// Check that available_callback works.
		add_theme_support( 'amp', array(
			'template_dir'       => 'amp-templates',
			'available_callback' => 'is_singular',
		) );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		query_posts( array( 's' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
	}

	/**
	 * Test finish_output_buffering.
	 *
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 */
	public function test_finish_output_buffering() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		ob_start();
		?>
		<!DOCTYPE html>
		<html amp <?php language_attributes(); ?>>
			<head>
				<?php wp_head(); ?>
				<script data-head>document.write('TODO: This needs to be sanitized as well once.');</script>
			</head>
			<body>
				<img width="100" height="100" src="https://example.com/test.png">
				<audio width="400" height="300" src="https://example.com/audios/myaudio.mp3"></audio>
				<amp-ad type="a9"
					width="300"
					height="250"
					data-aax_size="300x250"
					data-aax_pubname="test123"
					data-aax_src="302"></amp-ad>
				<?php wp_footer(); ?>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::finish_output_buffering( $original_html );

		$this->assertContains( '<meta charset="utf-8">', $sanitized_html );
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );
		$this->assertContains( '<style amp-boilerplate>', $sanitized_html );
		$this->assertContains( '<style amp-custom>', $sanitized_html );
		$this->assertContains( '<script async src="https://cdn.ampproject.org/v0.js"', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<meta name="generator" content="AMP Plugin', $sanitized_html );

		$this->assertNotContains( '<img', $sanitized_html );
		$this->assertContains( '<amp-img', $sanitized_html );

		$this->assertNotContains( '<audio', $sanitized_html );
		$this->assertContains( '<amp-audio', $sanitized_html );
		$this->assertContains( '<script async custom-element="amp-audio"', $sanitized_html );

		$this->assertContains( '<script async custom-element="amp-ad"', $sanitized_html );
	}
}
