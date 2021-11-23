<?php
/**
 * Test AMP_Bento_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\Dom\Document;

/**
 * Test AMP_Bento_Sanitizer.
 *
 * @coversDefaultClass AMP_Bento_Sanitizer
 */
class AMP_Bento_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/**
	 * Data for testing noscript handling.
	 *
	 * @return array
	 */
	public function get_sanitizer_data() {
		return [
			'bento-accordion-with-styles'   => [
				'source'               => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js"></script>
						</head>
						<body>
							<p>The following should have a green outline.</p>

							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-accordion-1.0.mjs"></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-accordion-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-accordion-1.0.css">
							<bento-accordion id="my-accordion" class="foo" data-bar="baz &amp; quux">
								<section>
									<h2>Section 1</h2>
									<div>Content in section 1.</div>
								</section>
								<section>
									<h2>Section 2</h2>
									<div>Content in section 2.</div>
								</section>
								<!-- Expanded on page load due to attribute: -->
								<section expanded="">
									<h2>Section 3</h2>
									<div>Content in section 3.</div>
								</section>
							</bento-accordion>

							<!-- Note the use of bento-accordion tag in the selector. When converted to amp-accordion, the selector must also be converted. -->
							<style>
								#my-accordion {
									display: block;
									outline: solid 2px red;
								}
								bento-accordion#my-accordion {
									outline: solid 2px green;
								}
								bento-accordion h2 {
									text-transform: uppercase;
								}
								bento-accordion .unknown {
									/* I should be tree-shaken because AMP_Bento_Sanitizer::has_light_shadow_dom() returns false. */
									color: red;
								}
							</style>
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<style amp-custom>#my-accordion{display:block;outline:solid 2px red}amp-accordion#my-accordion{outline:solid 2px green}amp-accordion h2{text-transform:uppercase}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<p>The following should have a green outline.</p>

							<amp-accordion id="my-accordion" class="foo" data-bar="baz &amp; quux">
								<section>
									<h2>Section 1</h2>
									<div>Content in section 1.</div>
								</section>
								<section>
									<h2>Section 2</h2>
									<div>Content in section 2.</div>
								</section>
								<!-- Expanded on page load due to attribute: -->
								<section expanded="">
									<h2>Section 3</h2>
									<div>Content in section 3.</div>
								</section>
							</amp-accordion>

							<!-- Note the use of bento-accordion tag in the selector. When converted to amp-accordion, the selector must also be converted. -->
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => true,
			],

			'non-amp-bento-component'       => [
				'source'               => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js"></script>
							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.mjs"></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-marquee-1.0.css">
						</head>
						<body>
							<bento-marquee id="marquee">
								<h2>News flash!!!</h2>
							</bento-marquee>

							<style>
								#marquee {
									display: block;
									outline: solid 2px red;
								}
								bento-marquee#marquee {
									outline: solid 2px green;
								}
								bento-marquee h2 {
									text-transform: uppercase;
								}
								bento-marquee .unknown {
									/* I should be tree-shaken because AMP_Bento_Sanitizer::has_light_shadow_dom() returns false. */
									color: red;
								}
							</style>
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs" data-px-verified-tag></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js" data-px-verified-tag></script>
							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.mjs" data-px-verified-tag></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.js" data-px-verified-tag></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-marquee-1.0.css" data-px-verified-tag data-px-verified-attrs="href">
							<style amp-custom>#marquee{display:block;outline:solid 2px red}bento-marquee#marquee{outline:solid 2px green}bento-marquee h2{text-transform:uppercase}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<bento-marquee data-px-verified-tag id="marquee">
								<h2>News flash!!!</h2>
							</bento-marquee>
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => true,
			],

			'both-amp-and-bento-components' => [
				'source'               => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js"></script>
							<script type="module" async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.mjs"></script>
							<script nomodule async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-timeago-1.0.css">
							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.mjs"></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-marquee-1.0.css">
							<style>
								#timeago {
									color: red;
								}
								bento-timeago#timeago {
									color: blue;
								}
								bento-timeago#timeago .unknown {
									color: green;
								}
								#marquee {
									display: block;
									outline: solid 2px red;
								}
								bento-marquee#marquee {
									outline: solid 2px green;
								}
								bento-marquee h2 {
									text-transform: uppercase;
								}
								bento-marquee .unknown {
									color: red;
								}
							</style>
						</head>
						<body>
						    <bento-timeago id="timeago" width="160" height="20" datetime="2017-04-11T00:37:33.809Z" locale="en">Saturday 11 April 2017 00.37</bento-timeago>
							<bento-marquee id="marquee">
								<h2>News flash!!!</h2>
							</bento-marquee>
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs" data-px-verified-tag></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js" data-px-verified-tag></script>
							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.mjs" data-px-verified-tag></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.js" data-px-verified-tag></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-marquee-1.0.css" data-px-verified-tag data-px-verified-attrs="href">
							<style amp-custom>#timeago{color:red}amp-timeago#timeago{color:blue}#marquee{display:block;outline:solid 2px red}bento-marquee#marquee{outline:solid 2px green}bento-marquee h2{text-transform:uppercase}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<amp-timeago id="timeago" width="160" height="20" datetime="2017-04-11T00:37:33.809Z" locale="en">Saturday 11 April 2017 00.37</amp-timeago>
							<bento-marquee data-px-verified-tag id="marquee">
								<h2>News flash!!!</h2>
							</bento-marquee>
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => true,
			],

			'no-bento-components'           => [
				'source'               => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js"></script>
							<script type="module" async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.mjs"></script>
							<script nomodule async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-timeago-1.0.css">
							<script type="module" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.mjs"></script>
							<script nomodule="" async="" src="https://cdn.ampproject.org/v0/bento-marquee-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-marquee-1.0.css">
							<style>
								body {
									color: green;
								}
								bento-timeago {
									color: blue;
								}
								bento-marquee h2 {
									text-transform: uppercase;
								}
								bento-marquee .unknown {
									color: red;
								}
							</style>
						</head>
						<body>
						    Hello World!!
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<style amp-custom>body{color:green}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							Hello World!!
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => false,
			],
		];
	}

	/**
	 * Test that noscript elements get replaced with their children.
	 *
	 * @dataProvider get_sanitizer_data
	 * @param string $source               Source.
	 * @param string $expected             Expected.
	 * @param array  $sanitizer_args       Sanitizer args.
	 * @param array  $expected_error_codes Expected error codes.
	 * @param bool   $expect_prefer_bento  Whether expecting prefer_bento to be set.
	 * @covers ::init()
	 * @covers ::sanitize()
	 * @covers ::has_light_shadow_dom()
	 * @covers ::get_selector_conversion_mapping()
	 */
	public function test_sanitize( $source, $expected = null, $sanitizer_args = [], $expected_error_codes = [], $expect_prefer_bento = false ) {
		if ( null === $expected ) {
			$expected = $source;
		}
		$dom = Document::fromHtml( $source, Options::DEFAULTS );

		$validation_error_callback_arg = isset( $sanitizer_args['validation_error_callback'] ) ? $sanitizer_args['validation_error_callback'] : null;

		$actual_error_codes = [];

		$sanitizer_args['validation_error_callback'] = static function ( $error ) use ( &$actual_error_codes, $validation_error_callback_arg ) {
			$actual_error_codes[] = $error['code'];

			if ( $validation_error_callback_arg ) {
				return $validation_error_callback_arg();
			} else {
				return true;
			}
		};

		$sanitizer_args['use_document_element'] = true;

		$bento_sanitizer = new AMP_Bento_Sanitizer(
			$dom,
			$sanitizer_args
		);

		$tag_and_attribute_sanitizer = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			array_merge(
				$sanitizer_args,
				[
					'prefer_bento' => false, // Overridden by AMP_Base_Sanitizer.
				]
			)
		);

		$sanitizers = [
			AMP_Bento_Sanitizer::class             => $bento_sanitizer,
			AMP_Script_Sanitizer::class            => new AMP_Script_Sanitizer(
				$dom,
				$sanitizer_args
			),
			AMP_Style_Sanitizer::class             => new AMP_Style_Sanitizer(
				$dom,
				$sanitizer_args
			),
			AMP_Tag_And_Attribute_Sanitizer::class => $tag_and_attribute_sanitizer,
		];

		foreach ( $sanitizers as $sanitizer ) {
			$sanitizer->init( $sanitizers );
		}

		$this->assertFalse( $tag_and_attribute_sanitizer->get_arg( 'prefer_bento' ) );

		foreach ( $sanitizers as $sanitizer ) {
			$sanitizer->sanitize();
		}

		$content = $dom->saveHTML( $dom->documentElement );
		$this->assertSimilarMarkup( $expected, $content );

		$this->assertSame( $expected_error_codes, $actual_error_codes );
		$this->assertSame( $expect_prefer_bento, $tag_and_attribute_sanitizer->get_arg( 'prefer_bento' ) );
	}
}
