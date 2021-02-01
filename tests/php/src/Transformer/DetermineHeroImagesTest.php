<?php

namespace AmpProject\AmpWP\Tests\Transformer;

use AmpProject\AmpWP\Tests\Helpers\ErrorComparison;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Transformer\DetermineHeroImages;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Transformer\DetermineHeroImages */
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
			'detects site icon'                            => [
				$input(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px">'
					. '</a>'
					. '</div>'
				),
				$output(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px" data-hero>'
					. '</a>'
					. '</div>'
				),
			],

			'detects featured image'                       => [
				$input(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '</div>'
					. '</figure>'
				),
				$output(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" data-hero>'
					. '</div>'
					. '</figure>'
				),
			],

			'detects cover blocks'                         => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)" data-hero><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)" data-hero><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '</div>'
				),
			],

			'site icons are prioritized over cover blocks' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px">'
					. '</a>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)" data-hero><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px" data-hero>'
					. '</a>'
					. '</div>'
				),
			],

			'featured images are prioritized over cover blocks' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)" data-hero><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" data-hero>'
					. '</div>'
				),
			],

			'pre-existing data-hero images are taken into account - 1' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img data-hero width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)" data-hero><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img data-hero width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '</div>'
				),
			],

			'pre-existing data-hero images are taken into account - 2' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img data-hero width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img data-hero width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px">'
					. '</a>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-background-dim alignleft" style="background-image:url(https://example.com/cover-block-1.jpg)"><p class="wp-block-cover-text">This is a left aligned cover block with a background image.</p></div>'
					. '<div class="wp-block-cover has-pale-pink-background-color has-background-dim has-left-content aligncenter" style="background-image:url(https://example.com/cover-block-2.jpg)"><p class="wp-block-cover-text"><strong>A center aligned cover image block, with a left aligned text.</strong></p></div>'
					. '<div class="wp-block-cover has-background-dim-20 has-background-dim has-parallax alignfull" style="background-image:url(https://example.com/cover-block-3.jpg)"><p class="wp-block-cover-text">This is a full width cover block with a fixed background image with a 20% opacity.</p></div>'
					. '<img data-hero width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img data-hero width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px">'
					. '</a>'
					. '</div>'
				),
			],
		];
	}

	/**
	 * Test the transform() method.
	 *
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::transform()
	 * @dataProvider data_transform()
	 *
	 * @param string                  $source          String of source HTML.
	 * @param string                  $expected_html   String of expected HTML
	 *                                                 output.
	 * @param ErrorCollection|Error[] $expected_errors Set of expected errors.
	 */
	public function test_transform( $source, $expected_html, $expected_errors = [] ) {
		$document    = Document::fromHtml( $source );
		$transformer = new DetermineHeroImages();
		$errors      = new ErrorCollection();

		$transformer->transform( $document, $errors );

		$this->assertSimilarMarkup( $expected_html, $document->saveHTML() );
		$this->assertSameErrors( $expected_errors, $errors );
	}
}
