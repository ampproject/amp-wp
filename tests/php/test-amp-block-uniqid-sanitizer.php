<?php
/**
 * Class AMP_Block_Uniqid_Sanitizer_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Test AMP_Block_Uniqid_Sanitizer
 *
 * @covers AMP_Block_Uniqid_Sanitizer
 */
class AMP_Block_Uniqid_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/** @return array */
	public function get_block_data() {
		return [
			'transform_duotone_class_name'           => [
				'content'  =>
					'
					<div class="wp-duotone-621e12fb51e3a wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>
					<style>.wp-duotone-621e12fb51e3a > .wp-block-cover__image-background, .wp-duotone-621e12fb51e3a > .wp-block-cover__video-background{filter:url(\'#wp-duotone-621e12fb51e3a\') !important;}</style>
					<svg xmlns="http://www.w3.org/2000/svg"><defs><filter id="wp-duotone-621e12fb51e3a"></filter></defs></svg>
					',
				'expected' =>
					'
					<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>
					<style>.wp-duotone-1 > .wp-block-cover__image-background, .wp-duotone-1 > .wp-block-cover__video-background{filter:url(\'#wp-duotone-1\') !important;}</style>
					<svg xmlns="http://www.w3.org/2000/svg"><defs><filter id="wp-duotone-1"></filter></defs></svg>
					',
			],
			'transform_legacy_duotone_class_name'    => [
				'content'  =>
					'
					<figure class="wp-duotone-filter-622b62d997e58 wp-block-image size-large">This is a super cool class name: <code>wp-duotone-filter-622b62d997e58</code>!</figure>
					<style>.wp-duotone-filter-622b62d997e58 img { filter: url( #wp-duotone-filter-622b62d997e58 ); }</style>
					<svg xmlns="http://www.w3.org/2000/svg"><defs><filter id="wp-duotone-filter-622b62d997e58"></filter></defs></svg>
					',
				'expected' =>
					'
					<figure class="wp-duotone-filter-1 wp-block-image size-large">This is a super cool class name: <code>wp-duotone-filter-622b62d997e58</code>!</figure>
					<style>.wp-duotone-filter-1 img { filter: url( #wp-duotone-filter-1 ); }</style>
					<svg xmlns="http://www.w3.org/2000/svg"><defs><filter id="wp-duotone-filter-1"></filter></defs></svg>
				',
			],
			'transform_container_class_name'         => [
				'content'  => '<div class="wp-container-621e133aaf0e2 wp-block-group is-style-default has-black-background-color has-background" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This is a super cool class name: <code>wp-container-0123456789abc</code>!</div>',
				'expected' => '<div class="wp-container-1 wp-block-group is-style-default has-black-background-color has-background" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This is a super cool class name: <code>wp-container-0123456789abc</code>!</div>',
			],
			'transform_elements_class_name'          => [
				'content'  =>
					'
					<p class="wp-elements-622941a2ca7ee has-text-align-right has-link-color">This is a super cool class name: <code>wp-elements-622941a2ca7ee</code>!</p>
					<style>.wp-elements-622941a2ca7ee a{color: var(--wp--preset--color--primary);}</style>
					',
				'expected' =>
					'
					<p class="wp-elements-1 has-text-align-right has-link-color">This is a super cool class name: <code>wp-elements-622941a2ca7ee</code>!</p>
					<style>.wp-elements-1 a{color: var(--wp--preset--color--primary);}</style>
					',
			],
			'ignore_class_names_without_hash'        => [
				'content'  => '<div class="wp-duotone-test wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'expected' => '<div class="wp-duotone-test wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
			],
			'ignore_already_transformed_class_names' => [
				'content'  => '<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'expected' => '<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
			],
		];
	}

	/**
	 * @covers ::transform_class_names_in_content()
	 * @covers ::transform_class_names_in_inline_styles()
	 * @covers ::get_class_name_regexp_pattern()
	 * @covers ::unique_id()
	 *
	 * @dataProvider get_block_data
	 *
	 * @param string $content
	 * @param string $expected
	 */
	public function test_sanitize( $content, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $content );
		$sanitizer = new AMP_Block_Uniqid_Sanitizer( $dom );

		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		// Normalize auto-incrementing ID to allow tests to be run in isolation.
		$content = preg_replace( '/-\d+\b/', '-1', $content );

		$this->assertEqualMarkup( $expected, $content );
	}
}
