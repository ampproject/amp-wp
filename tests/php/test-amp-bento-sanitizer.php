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
			'bento-accordion-with-styles'       => [
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
							<bento-accordion id="my-accordion" class="foo" data-bar="baz &amp; quux" style="border: solid 1px black">
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
							<style amp-custom>#my-accordion{display:block;outline:solid 2px red}amp-accordion#my-accordion{outline:solid 2px green}amp-accordion h2{text-transform:uppercase}:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-a33452e{border:solid 1px black}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<p>The following should have a green outline.</p>

							<amp-accordion id="my-accordion" class="foo amp-wp-a33452e" data-amp-original-style="border: solid 1px black" data-bar="baz &amp; quux">
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

			'non-amp-bento-component'           => [
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

			'both-amp-and-bento-components'     => [
				'source'               => '
					<html>
						<head>
							<meta charset="utf-8">
							<script type="module" async="" src="https://cdn.ampproject.org/bento.mjs"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js"></script>
							<script type="module" async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.mjs"></script>
							<script nomodule async src="https://cdn.ampproject.org/v0/bento-timeago-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-instagram-1.0.css">
							<script type="module" async src="https://cdn.ampproject.org/v0/bento-instagram-1.0.mjs"></script>
							<script nomodule async src="https://cdn.ampproject.org/v0/bento-instagram-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-timeago-1.0.css">
							<script type="module" src="https://cdn.ampproject.org/v0/bento-soundcloud-1.0.mjs" crossorigin="anonymous"></script>
							<script nomodule src="https://cdn.ampproject.org/v0/bento-soundcloud-1.0.js" crossorigin="anonymous"></script>
							<link rel="stylesheet" href="https://cdn.ampproject.org/v0/bento-soundcloud-1.0.css" crossorigin="anonymous">
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
						    <bento-timeago id="timeago" style="height: 30px; border: solid 1px green;" datetime="2017-04-11T00:37:33.809Z" locale="en">Saturday 11 April 2017 00.37</bento-timeago>
							<bento-instagram id="my-instagram1" data-shortcode="CKXYAzuj7TE" data-captioned style="height: 800px; width: 400px"></bento-instagram>
							<bento-instagram id="my-instagram2" data-shortcode="CKXYAzuj7TE" data-captioned style="height: 80em; width: 40em"></bento-instagram>
							<bento-soundcloud id="sc1" data-trackid="89299804" data-visual="true" style="aspect-ratio: 16 / 9"></bento-soundcloud>
							<bento-soundcloud id="sc2" data-trackid="89299804" data-visual="true" style="aspect-ratio: 9.12 / 16.34"></bento-soundcloud>
							<bento-soundcloud id="sc3" data-trackid="89299804" data-visual="true" style="aspect-ratio: 0.5"></bento-soundcloud>
							<bento-soundcloud id="sc4" data-trackid="89299804" data-visual="true" style="aspect-ratio: 2 / 1; max-width: 500px;"></bento-soundcloud>
							<bento-soundcloud id="sc5" data-trackid="89299804" data-visual="true" style="aspect-ratio: 1 / 2; max-width: 500px;"></bento-soundcloud>
							<bento-soundcloud id="sc6" data-trackid="89299804" data-visual="true" style="aspect-ratio: 1 / 2; max-width: 50vw;"></bento-soundcloud>
							<bento-wordpress-embed id="my-embed" style="width:100%; height: 400px" data-url="https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/"></bento-wordpress-embed>
							<div style="position:relative; width:100%; height: 400px;">
								<bento-wordpress-embed id="my-fill-embed" style="position:absolute; width:100%; height: 100%;" data-url="https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/"></bento-wordpress-embed>
							</div>
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
							<style amp-custom>#timeago{color:red}amp-timeago#timeago{color:blue}#marquee{display:block;outline:solid 2px red}bento-marquee#marquee{outline:solid 2px green}bento-marquee h2{text-transform:uppercase}:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-c437205{border:solid 1px green}:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-4d7586b{max-width:50vw}:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-4ce7767{position:relative;width:100%;height:400px}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<amp-timeago class="amp-wp-c437205" data-amp-original-style="border:solid 1px green" id="timeago" height="30" width="auto" layout="fixed-height" datetime="2017-04-11T00:37:33.809Z" locale="en">Saturday 11 April 2017 00.37</amp-timeago>
							<amp-instagram id="my-instagram1" data-shortcode="CKXYAzuj7TE" data-captioned width="400" height="800"></amp-instagram>
							<amp-instagram id="my-instagram2" data-shortcode="CKXYAzuj7TE" data-captioned width="40em" height="80em"></amp-instagram>
							<amp-soundcloud id="sc1" data-trackid="89299804" data-visual="true" height="9" width="16" layout="responsive"></amp-soundcloud>
							<amp-soundcloud id="sc2" data-trackid="89299804" data-visual="true" height="16.34" width="9.12" layout="responsive"></amp-soundcloud>
							<amp-soundcloud id="sc3" data-trackid="89299804" data-visual="true" height="1" width="0.5" layout="responsive"></amp-soundcloud>
							<amp-soundcloud id="sc4" data-trackid="89299804" data-visual="true" height="250" width="500" layout="intrinsic"></amp-soundcloud>
							<amp-soundcloud id="sc5" data-trackid="89299804" data-visual="true" height="1000" width="500" layout="intrinsic"></amp-soundcloud>
							<amp-soundcloud id="sc6" class="amp-wp-4d7586b" data-amp-original-style="max-width:50vw" data-trackid="89299804" data-visual="true" height="2" width="1" layout="responsive"></amp-soundcloud>
							<amp-wordpress-embed id="my-embed" height="400" width="auto" layout="fixed-height" data-url="https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/"></amp-wordpress-embed>
							<div class="amp-wp-4ce7767" data-amp-original-style="position:relative; width:100%; height: 400px;">
								<amp-wordpress-embed data-url="https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/" id="my-fill-embed" layout="fill"></amp-wordpress-embed>
							</div>

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

			'bento-components-hidden-initially' => [
				'source'               => '
					<html>
						<head>
							<script nomodule src="https://cdn.ampproject.org/bento.js"></script>
							<script type="module" async src="https://cdn.ampproject.org/bento.mjs"></script>
							<script type="module" src="https://cdn.ampproject.org/v0/bento-sidebar-1.0.mjs" crossorigin="anonymous"></script>
							<script nomodule src="https://cdn.ampproject.org/v0/bento-sidebar-1.0.js" crossorigin="anonymous"></script>
							<link rel="stylesheet" href="https://cdn.ampproject.org/v0/bento-sidebar-1.0.css" crossorigin="anonymous">
							<script type="module" async src="https://cdn.ampproject.org/v0/bento-lightbox-1.0.mjs"></script>
							<script nomodule async src="https://cdn.ampproject.org/v0/bento-lightbox-1.0.js"></script>
							<link rel="stylesheet" type="text/css" href="https://cdn.ampproject.org/v0/bento-lightbox-1.0.css">
						</head>
						<body>
							<bento-sidebar id="sidebar1" side="left">
								<button id="close-sidebar">Close</button>
							</bento-sidebar>
							<bento-sidebar id="sidebar2" side="left" hidden>
								<button id="close-sidebar">Close</button>
							</bento-sidebar>
							<bento-lightbox id="my-lightbox">
								Lightboxed content
								<button id="close-button">Close lightbox</button>
							</bento-lightbox>
							<bento-instagram id="my-instagram" data-shortcode="CKXYAzuj7TE" data-captioned hidden></bento-instagram>
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
						</head>
						<body>
							<amp-sidebar id="sidebar1" side="left" layout="nodisplay">
								<button id="close-sidebar">Close</button>
							</amp-sidebar>
							<amp-sidebar id="sidebar2" side="left" layout="nodisplay">
								<button id="close-sidebar">Close</button>
							</amp-sidebar>
							<amp-lightbox id="my-lightbox" layout="nodisplay">
								Lightboxed content
								<button id="close-button">Close lightbox</button>
							</amp-lightbox>
							<amp-instagram id="my-instagram" data-shortcode="CKXYAzuj7TE" data-captioned layout="nodisplay"></amp-instagram>
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => true,
			],

			'no-bento-components'               => [
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

							<script type="module" async src="https://cdn.ampproject.org/v0/bento-invalid.mjs"></script>
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
				'expected_error_codes' => [ AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG ],
				'expect_prefer_bento'  => false,
			],

			'bento-mathml'                      => [
				'source'               => '
					<html>
						<head></head>
						<body>
							<script type="module" src="https://cdn.ampproject.org/bento.mjs" crossorigin="anonymous"></script>
							<script nomodule src="https://cdn.ampproject.org/bento.js" crossorigin="anonymous"></script>
							<script type="module" src="https://cdn.ampproject.org/v0/bento-mathml-1.0.mjs" crossorigin="anonymous"></script>
							<script nomodule src="https://cdn.ampproject.org/v0/bento-mathml-1.0.js" crossorigin="anonymous"></script>
							<link rel="stylesheet" href="https://cdn.ampproject.org/v0/bento-mathml-1.0.css" crossorigin="anonymous">

							<h2>The Quadratic Formula</h2>
							<bento-mathml
							  style="height: 40px"
							  data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"
							></bento-mathml>

							<h2>Inline formula</h2>
							<p>
							  This is an example of a formula,
							  <bento-mathml
							    style="height: 40px; width: 147px"
							    inline
							    data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"
							  ></bento-mathml>
							  placed inline in the middle of a block of text.
							</p>
						</body>
					</html>
				',
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<style amp-custom>:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-3ef80bd{height:40px}:root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-120213e{height:40px;width:147px}

							/*# sourceURL="amp-custom.css" */</style>
						</head>
						<body>
							<h2>The Quadratic Formula</h2>
							<amp-mathml class="amp-wp-3ef80bd" data-amp-original-style="height: 40px" data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>

							<h2>Inline formula</h2>
							<p>
							  This is an example of a formula,
							  <amp-mathml class="amp-wp-120213e" data-amp-original-style="height: 40px; width: 147px" inline data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>
							  placed inline in the middle of a block of text.
							</p>
						</body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [],
				'expect_prefer_bento'  => true,
			],

			'bento-no-layout-available'         => [
				'source'               => '
					<html>
						<head>
							<script type="module" src="https://cdn.ampproject.org/bento.mjs" crossorigin="anonymous"></script>
							<script nomodule="" src="https://cdn.ampproject.org/bento.js" crossorigin="anonymous"></script>

							<script type="module" src="https://cdn.ampproject.org/v0/bento-base-carousel-1.0.mjs" crossorigin="anonymous"></script>
							<script nomodule="" src="https://cdn.ampproject.org/v0/bento-base-carousel-1.0.js" crossorigin="anonymous"></script>

							<link rel="stylesheet" href="https://cdn.ampproject.org/v0/bento-base-carousel-1.0.css" crossorigin="anonymous">

							<style>
							bento-base-carousel,
							bento-base-carousel div {
								aspect-ratio: 4/1;
							}
							.red {
								background: darkred;
							}
							.blue {
								background: steelblue;
							}
							.green {
								background: seagreen;
							}
							</style>
						</head>
						<body>
							<bento-base-carousel id="my-carousel">
								<div class="red"></div>
								<div class="blue"></div>
								<div class="green"></div>
							</bento-base-carousel>
						</body>
					</html>
				',
				// Note: It's somewhat unexpected that the styles are still in the document since the element was removed.
				// But this is because the tree shaking happened before the tag-and-attribute sanitizer ran, so at the
				// time of tree shaking the bento-base-carousel element was still in the document.
				'expected'             => '
					<html>
						<head>
							<meta charset="utf-8">
							<style amp-custom>amp-base-carousel,amp-base-carousel div{aspect-ratio:4/1}.red{background:darkred}.blue{background:steelblue}.green{background:seagreen} /*# sourceURL="amp-custom.css" */</style>
						</head>
						<body></body>
					</html>
				',
				'sanitizer_args'       => [],
				'expected_error_codes' => [
					AMP_Tag_And_Attribute_Sanitizer::MISSING_LAYOUT_ATTRIBUTES,
				],
				'expect_prefer_bento'  => true,
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
	 * @covers ::adapt_layout_styles()
	 * @covers ::get_bento_component_name_from_url()
	 * @covers \AMP_Base_Sanitizer::set_layout()
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
