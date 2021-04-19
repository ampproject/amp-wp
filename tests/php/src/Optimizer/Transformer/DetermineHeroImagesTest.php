<?php

namespace AmpProject\AmpWP\Tests\Optimizer\Transformer;

use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Tests\Helpers\ErrorComparison;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages */
final class DetermineHeroImagesTest extends WP_UnitTestCase {

	use ErrorComparison;
	use MarkupComparison;

	/**
	 * Provide the data to test the transform() method.
	 *
	 * @return array[] Associative array of data arrays.
	 */
	public function data_transform() {
		$input = static function ( $body ) {
			return '<!DOCTYPE html><html ⚡><head>'
				. '<meta charset="utf-8">'
				. '</head><body>'
				. $body
				. '</body></html>';
		};

		$output = static function ( $body ) {
			return '<!DOCTYPE html><html ⚡><head>'
				. '<meta charset="utf-8">'
				. '</head><body>'
				. $body
				. '</body></html>';
		};

		return [
			'detects custom header as in document with main' => [
				$input(
					'<header>'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg"></amp-img>'
					. '</header><main></main>'
				),
				$output(
					'<header>'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg" data-hero-candidate></amp-img>'
					. '</header><main></main>'
				),
			],

			'detects custom header as in document with entry-content' => [
				$input(
					'<div class="my-header">'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg"></amp-img>'
					. '</div><div class="hfeed"><div class="hentry"><div class="entry-content"></div></div></div>'
				),
				$output(
					'<div class="my-header">'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg" data-hero-candidate></amp-img>'
					. '</div><div class="hfeed"><div class="hentry"><div class="entry-content"></div></div></div>'
				),
			],

			'ignores header image which was originally lazy-loaded' => [
				$input(
					'<header>'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg"><noscript><img loading="lazy" width="789" height="539" src="https://example.com/custom-header.jpg"></noscript></amp-img>'
					. '</header><main></main>'
				),
				$output(
					'<header>'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg"><noscript><img loading="lazy" width="789" height="539" src="https://example.com/custom-header.jpg"></noscript></amp-img>'
					. '</header><main></main>'
				),
			],

			'ignores image after main'                     => [
				$input(
					'<main></main><footer>'
					. '<amp-img width="789" height="539" src="https://example.com/banner.jpg"></amp-img>'
					. '</footer>'
				),
				$output(
					'<main></main><footer>'
					. '<amp-img width="789" height="539" src="https://example.com/banner.jpg"></amp-img>'
					. '</footer>'
				),
			],

			'detects first content cover block'            => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" data-hero-candidate alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],

			'detects first content image block'            => [
				$input(
					'<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><amp-img loading="lazy" width="1024" height="768" src="https://example.com/image-block-1.jpg" alt="" class="wp-image-2135"></amp-img></figure>'
					. '</div>'
					. '<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><amp-img loading="lazy" width="1024" height="768" src="https://example.com/image-block-2.jpg" alt="" class="wp-image-2135"></amp-img></figure>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><amp-img loading="lazy" width="1024" height="768" src="https://example.com/image-block-1.jpg" alt="" class="wp-image-2135" data-hero-candidate></amp-img></figure>'
					. '</div>'
					. '<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><amp-img loading="lazy" width="1024" height="768" src="https://example.com/image-block-2.jpg" alt="" class="wp-image-2135"></amp-img></figure>'
					. '</div>'
				),
			],

			'detects first cover block in initial group'   => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-group"><div class="wp-block-group__inner-container">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-group"><div class="wp-block-group__inner-container">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" data-hero-candidate alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div></div>'
					. '</div>'
				),
			],

			'detects first YouTube block in initial group' => [
				$input(
					'<div class="entry-content">'
					. '<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube"><div class="wp-block-embed__wrapper">'
					. '<amp-youtube data-videoid="xrGAQCq9BMU" layout="responsive" width="16" height="9" title="NASA at Saturn: Cassini\'s Grand Finale" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:56.25%"></i-amphtml-sizer><a placeholder href="https://youtu.be/xrGAQCq9BMU"><amp-img src="https://i.ytimg.com/vi/xrGAQCq9BMU/hqdefault.jpg" layout="fill" object-fit="cover" alt="NASA at Saturn: Cassini\'s Grand Finale" class="amp-wp-enforced-sizes i-amphtml-layout-fill i-amphtml-layout-size-defined"></amp-img></a></amp-youtube>'
					. '</div></figure>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube"><div class="wp-block-embed__wrapper">'
					. '<amp-youtube data-videoid="xrGAQCq9BMU" layout="responsive" width="16" height="9" title="NASA at Saturn: Cassini\'s Grand Finale" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:56.25%"></i-amphtml-sizer><a placeholder href="https://youtu.be/xrGAQCq9BMU"><amp-img src="https://i.ytimg.com/vi/xrGAQCq9BMU/hqdefault.jpg" data-hero-candidate layout="fill" object-fit="cover" alt="NASA at Saturn: Cassini\'s Grand Finale" class="amp-wp-enforced-sizes i-amphtml-layout-fill i-amphtml-layout-size-defined"></amp-img></a></amp-youtube>'
					. '</div></figure>'
					. '</div>'
				),
			],

			'ignores tiny image at beginning of entry content' => [
				$input(
					'<div class="entry-content">'
					. '<p><amp-img src="https://s.w.org/images/core/emoji/13.0.1/72x72/1f642.png" alt="🙂" class="wp-smiley amp-wp-enforced-sizes amp-wp-843f19c i-amphtml-layout-intrinsic i-amphtml-layout-size-defined" width="72" height="72" noloading="" layout="intrinsic" data-amp-original-style="height: 1em; max-height: 1em;"></amp-img></p>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<p><amp-img src="https://s.w.org/images/core/emoji/13.0.1/72x72/1f642.png" alt="🙂" class="wp-smiley amp-wp-enforced-sizes amp-wp-843f19c i-amphtml-layout-intrinsic i-amphtml-layout-size-defined" width="72" height="72" noloading="" layout="intrinsic" data-amp-original-style="height: 1em; max-height: 1em;"></amp-img></p>'
					. '</div>'
				),
			],

			'ignores non-initial cover blocks'             => [
				$input(
					'<div class="entry-content">'
					. '<p>Another block at beginning!</p>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<p>Another block at beginning!</p>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],

			'site icon and custom header are prioritized over content block' => [
				$input(
					'<header><a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div></header>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<header><a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img data-hero-candidate width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img data-hero-candidate width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div></header>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],

			'featured image and custom header prioritized over cover blocks' => [
				$input(
					'<header><a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div></header>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<header><a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img data-hero-candidate width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img data-hero-candidate width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div></header>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><amp-img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"></amp-img><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],
		];
	}

	/**
	 * Test the transform() method.
	 *
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::transform()
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::add_data_hero_candidate_attribute()
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::get_custom_header()
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::get_custom_logo()
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::get_initial_content_image_block()
	 * @covers       \AmpProject\AmpWP\Optimizer\Transformer\DetermineHeroImages::get_initial_content_cover_block()
	 * @dataProvider data_transform()
	 *
	 * @param string                  $source          String of source HTML.
	 * @param string                  $expected_html   String of expected HTML
	 *                                                 output.
	 * @param ErrorCollection|Error[] $expected_errors Set of expected errors.
	 */
	public function test_transform( $source, $expected_html, $expected_errors = [] ) {
		$document    = Document::fromHtml( $source, Options::DEFAULTS );
		$transformer = new DetermineHeroImages();
		$errors      = new ErrorCollection();

		$transformer->transform( $document, $errors );

		$this->assertSimilarMarkup( $expected_html, $document->saveHTML() );
		$this->assertSameErrors( $expected_errors, $errors );
	}
}
