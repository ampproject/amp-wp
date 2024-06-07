<?php
/**
 * Test AMP_Script_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\ValidationExemption;
use AmpProject\DevMode;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;

/**
 * Test AMP_Script_Sanitizer.
 *
 * @coversDefaultClass AMP_Script_Sanitizer
 */
class AMP_Script_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/**
	 * Data for testing noscript handling.
	 *
	 * @return array
	 */
	public function get_sanitizer_data() {
		return [
			'document_write'                => [
				'<html><head><meta charset="utf-8"></head><body>Has script? <script>document.write("Yep!")</script><noscript>Nope!</noscript></body></html>',
				'<html><head><meta charset="utf-8"></head><body>Has script? <!--noscript-->Nope!<!--/noscript--></body></html>',
				[],
				[ AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG ],
			],
			'nested_elements'               => [
				'<html><head><meta charset="utf-8"></head><body><noscript>before <em><strong>middle</strong> end</em></noscript></body></html>',
				'<html><head><meta charset="utf-8"></head><body><!--noscript-->before <em><strong>middle</strong> end</em><!--/noscript--></body></html>',
			],
			'head_noscript_style'           => [
				'<html><head><meta charset="utf-8"><noscript><style>body{color:red}</style></noscript></head><body></body></html>',
				'<html><head><meta charset="utf-8"><!--noscript--><style>body{color:red}</style><!--/noscript--></head><body></body></html>',
			],
			'head_noscript_span'            => [
				'<html><head><meta charset="utf-8"><noscript><span>No script</span></noscript></head><body></body></html>',
				'<html><head><meta charset="utf-8"></head><body><!--noscript--><span>No script</span><!--/noscript--></body></html>',
			],
			'test_with_dev_mode'            => [
				'<html data-ampdevmode=""><head><meta charset="utf-8"></head><body><noscript data-ampdevmode="">hey</noscript></body></html>',
				null,
			],
			'noscript_no_unwrap_attr'       => [
				'<html><head><meta charset="utf-8"></head><body><noscript data-amp-no-unwrap><span>No script</span></noscript></body></html>',
				null,
			],
			'noscript_no_unwrap_arg'        => [
				'<html><head><meta charset="utf-8"></head><body><noscript><span>No script</span></noscript></body></html>',
				null,
				[
					'unwrap_noscripts' => false,
				],
			],
			'script_kept_no_unwrap'         => [
				'
					<html><head><meta charset="utf-8"></head><body>
						<script>document.write("Hey.")</script>
						<noscript data-amp-no-unwrap><span>No script</span></noscript>
					</body></html>',
				sprintf(
					'
					<html><head><meta charset="utf-8"></head><body>
						<script %s>document.write("Hey.")</script>
						<noscript data-amp-no-unwrap><span>No script</span></noscript>
					</body></html>
					',
					ValidationExemption::AMP_UNVALIDATED_TAG_ATTRIBUTE
				),
				[
					'sanitize_js_scripts'       => true,
					'unwrap_noscripts'          => true, // This will be ignored because of the kept script.
					'validation_error_callback' => '__return_false',
				],
				[
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
				],
			],
			'inline_scripts_removed'        => [
				'
					<html><head><meta charset="utf-8"></head><body>
						<script>document.write("Hey")</script>
						<script type="text/javascript">document.write("Hey")</script>
						<script type="text/ecmascript">document.write("Hey")</script>
						<script type="application/javascript">document.write("Hey")</script>
						<script type="application/ecmascript">document.write("Hey")</script>
						<script type="module">document.write("Hey")</script>
						<script type="application/json">{"data":1}</script>
						<script type="application/ld+json">{"data":2}</script>
						<amp-state id="test"><script type="application/json">{"data":3}</script></amp-state>
						<amp-script width="200" height="100" script="hello-world"><button>Hello amp-script!</button></amp-script>
						<script id="hello-world" type="text/plain" target="amp-script">
							/* This is perfectly fine! */
						</script>
						<script type="text/plain" template="amp-mustache">Hello {{world}}!</script>
					</body></html>
				',
				'
					<html><head><meta charset="utf-8"></head><body>
						<script type="application/ld+json">{"data":2}</script>
						<amp-state id="test"><script type="application/json">{"data":3}</script></amp-state>
						<amp-script width="200" height="100" script="hello-world"><button>Hello amp-script!</button></amp-script>
						<script id="hello-world" type="text/plain" target="amp-script">
							/* This is perfectly fine! */
						</script>
						<script type="text/plain" template="amp-mustache">Hello {{world}}!</script>
					</body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG,
				],
			],
			'external_scripts_removed'      => [
				'
					<html>
					<head><meta charset="utf-8"></head>
					<body>
						<script src="https://example.com/1"></script>
						<script type="text/javascript" src="https://example.com/2"></script>
						<script type="module" src="https://example.com/3"></script>
					</body></html>
				',
				'
					<html>
					<head><meta charset="utf-8"></head>
					<body></body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
				],
			],
			'external_scripts_kept'         => [
				'
					<html>
					<head><meta charset="utf-8"></head>
					<body>
						<script src="https://example.com/1"></script>
						<script type="text/javascript" src="https://example.com/2"></script>
						<script type="module" src="https://example.com/3"></script>
					</body></html>
				',
				sprintf(
					'
						<html>
						<head><meta charset="utf-8"></head>
						<body>
							<script src="https://example.com/1" %1$s></script>
							<script type="text/javascript" src="https://example.com/2" %1$s></script>
							<script type="module" src="https://example.com/3" %1$s></script>
						</body></html>
					',
					ValidationExemption::AMP_UNVALIDATED_TAG_ATTRIBUTE
				),
				[
					'sanitize_js_scripts'       => true,
					'validation_error_callback' => '__return_false',
				],
				[
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
				],
			],
			'external_devmode_script_kept'  => [
				'
					<html data-ampdevmode>
					<head><meta charset="utf-8"></head>
					<body>
						<script data-ampdevmode src="https://example.com/1"></script>
					</body></html>
				',
				null,
				[
					'sanitize_js_scripts' => true,
				],
				[],
			],
			'external_amp_script_kept'      => [
				'
					<html>
					<head>
						<meta charset="utf-8">
						<script async src="https://cdn.ampproject.org/v0.js" crossorigin="anonymous"></script>
					</head>
					<body></body></html>
				',
				null,
				[
					'sanitize_js_scripts' => true,
				],
				[],
			],
			'amp_onerror_script_kept'       => [
				'
					<html>
					<head>
						<meta charset="utf-8">
						<script amp-onerror>document.querySelector("script[src*=\'/v0.js\']").onerror=function(){document.querySelector(\'style[amp-boilerplate]\').textContent=\'\'}</script>
					</head>
					<body></body></html>
				',
				null,
				[
					'sanitize_js_scripts' => true,
				],
				[],
			],
			'inline_event_handler_removed'  => [
				'
					<html><head><meta charset="utf-8"></head>
					<body onload="alert(\'Hey there.\')">
						<noscript>I should get unwrapped.</noscript>
						<div id="warning-message">Warning...</div>
						<button on="tap:warning-message.hide">Cool, thanks!</button>
					</body></html>
				',
				'
					<html><head><meta charset="utf-8"></head>
					<body>
						<!--noscript-->I should get unwrapped.<!--/noscript-->
						<div id="warning-message">Warning...</div>
						<button on="tap:warning-message.hide">Cool, thanks!</button>
					</body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[
					AMP_Script_Sanitizer::CUSTOM_EVENT_HANDLER_ATTR,
				],
			],
			'inline_event_handler_kept'     => [
				'
					<html><head><meta charset="utf-8"></head>
					<body onload="alert(\'Hey there.\')">
						<noscript>I should not get unwrapped.</noscript>
						<div id="warning-message">Warning...</div>
						<button on="tap:warning-message.hide">Cool, thanks!</button>
					</body></html>
				',
				sprintf(
					'
						<html><head><meta charset="utf-8"></head>
						<body %s="onload" onload="alert(\'Hey there.\')">
							<noscript>I should not get unwrapped.</noscript>
							<div id="warning-message">Warning...</div>
							<button on="tap:warning-message.hide">Cool, thanks!</button>
						</body></html>
					',
					ValidationExemption::AMP_UNVALIDATED_ATTRS_ATTRIBUTE
				),
				[
					'sanitize_js_scripts'       => true,
					'validation_error_callback' => '__return_false',
				],
				[
					AMP_Script_Sanitizer::CUSTOM_EVENT_HANDLER_ATTR,
				],
			],
			'event_handler_lookalikes_kept' => [
				'
					<html><head><meta charset="utf-8"></head>
					<body>
						<noscript>I should get unwrapped.</noscript>
						<div id="warning-message">Warning...</div>
						<button on="tap:warning-message.hide">Cool, thanks!</button>
						<amp-position-observer intersection-ratios="0.5" on="enter:clockAnim.start;exit:clockAnim.pause" layout="nodisplay" once></amp-position-observer>
						<amp-font layout="nodisplay" font-family="My Font" timeout="3000" on-error-remove-class="my-font-loading" on-error-add-class="my-font-missing"></amp-font>
					</body></html>
				',
				'
					<html><head><meta charset="utf-8"></head>
					<body>
						<!--noscript-->I should get unwrapped.<!--/noscript-->
						<div id="warning-message">Warning...</div>
						<button on="tap:warning-message.hide">Cool, thanks!</button>
						<amp-position-observer intersection-ratios="0.5" on="enter:clockAnim.start;exit:clockAnim.pause" layout="nodisplay" once></amp-position-observer>
						<amp-font layout="nodisplay" font-family="My Font" timeout="3000" on-error-remove-class="my-font-loading" on-error-add-class="my-font-missing"></amp-font>
					</body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[],
			],
		];
	}

	/**
	 * Test that noscript elements get replaced with their children.
	 *
	 * @dataProvider get_sanitizer_data
	 * @param string $source        Source.
	 * @param string $expected      Expected.
	 * @param array $sanitizer_args Sanitizer args.
	 * @covers ::sanitize()
	 */
	public function test_sanitize( $source, $expected = null, $sanitizer_args = [], $expected_error_codes = [] ) {
		if ( null === $expected ) {
			$expected = $source;
		}
		$dom = Document::fromHtml( $source, Options::DEFAULTS );
		$this->assertSame( substr_count( $source, '<noscript' ), $dom->getElementsByTagName( 'noscript' )->length );

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

		$sanitizer = new AMP_Script_Sanitizer( $dom, $sanitizer_args );
		$sanitizer->sanitize();
		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, $sanitizer_args );
		$validating_sanitizer->sanitize();
		$content = $dom->saveHTML( $dom->documentElement );
		$this->assertSimilarMarkup( $expected, $content );

		$this->assertSame( $expected_error_codes, $actual_error_codes );
	}

	/** @return array */
	public function get_data_to_test_cascading_sanitizer_argument_changes_with_custom_scripts() {
		return [
			'custom_scripts_removed'         => [ 3 ],
			'custom_scripts_px_verified'     => [ 2 ],
			'custom_scripts_amp_unvalidated' => [ 1 ],
		];
	}

	/**
	 * @dataProvider get_data_to_test_cascading_sanitizer_argument_changes_with_custom_scripts
	 *
	 * @covers ::init()
	 *
	 * @param int $level Level.
	 */
	public function test_cascading_sanitizer_argument_changes_with_custom_scripts( $level ) {

		add_filter(
			'pre_http_request',
			static function ( $preempt, $request, $url ) {
				$r = [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'headers'  => [ 'content-type' => 'text/css' ],
				];
				if ( 'https://example.com/head.css' === $url ) {
					$preempt = array_merge(
						$r,
						[ 'body' => 'head{background-color:white}' ]
					);
				} elseif ( 'https://example.com/body.css' === $url ) {
					$preempt = array_merge(
						$r,
						[ 'body' => 'body{background-color:black}' ]
					);
				}
				return $preempt;
			},
			10,
			3
		);

		switch ( $level ) {
			case 3:
				$tag_exemption_attribute  = '';
				$attr_exemption_attribute = '';
				break;
			case 2:
				$tag_exemption_attribute  = ValidationExemption::PX_VERIFIED_TAG_ATTRIBUTE;
				$attr_exemption_attribute = ValidationExemption::PX_VERIFIED_ATTRS_ATTRIBUTE . '="onload"';
				break;
			default:
				$tag_exemption_attribute  = ValidationExemption::AMP_UNVALIDATED_TAG_ATTRIBUTE;
				$attr_exemption_attribute = ValidationExemption::AMP_UNVALIDATED_ATTRS_ATTRIBUTE . '="onload"';
		}

		// @todo In level 1, deny-listed CSS properties like -moz-binding and behavior should be allowed. Nevertheless, these are very rare now (since they are obsolete).
		$dom = Document::fromHtml(
			sprintf(
				'
				<html>
					<head>
						<style>
						/*comment*/
						body { background: red; }
						body.loaded { background: green; }
						img { outline: solid 1px red; }
						audio { outline: solid 1px green !important; }
						video { outline: solid 1px blue; }
						iframe { outline: solid 1px black; }
						</style>
						<link rel="stylesheet" type="text/css" href="https://example.com/head.css">
					</head>
					<body onload="doSomething()" %s>
						<img src="https://example.com/logo.png" width="300" height="100" alt="Logo">
						<audio src="https://example.com/music.mp3" width="300" height="100"></audio>
						<video src="https://example.com/movie.mp4" width="640" height="480"></video>
						<iframe src="https://example.com/" width="100" height="400"></iframe>
						<amp-facebook-page width="340" height="130" layout="responsive" data-href="https://www.facebook.com/imdb/"></amp-facebook-page>
						<form action="https://example.com/subscribe/" method="post">
							<input type="email" name="email">
						</form>
						<div id="blueviolet" style="color:blueviolet !important;">Blue Violet</div>
						<script %s>document.addEventListener("DOMContentLoaded", () => document.body.classList.add("loaded"))</script>
						<link rel="stylesheet" href="https://example.com/body.css">
						<style>
						body:after{content:' . str_repeat( 'a', 75000 ) . '}
						</style>
					</body>
				</html>
				',
				$attr_exemption_attribute,
				$tag_exemption_attribute
			),
			Options::DEFAULTS
		);

		$sanitizers = [
			AMP_Script_Sanitizer::class            => new AMP_Script_Sanitizer(
				$dom,
				[
					'sanitize_js_scripts' => true,
				]
			),
			AMP_Form_Sanitizer::class              => new AMP_Form_Sanitizer(
				$dom,
				[
					'native_post_forms_allowed' => 'never', // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Img_Sanitizer::class               => new AMP_Img_Sanitizer(
				$dom,
				[
					'native_img_used' => false, // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Video_Sanitizer::class             => new AMP_Video_Sanitizer(
				$dom,
				[
					'native_video_used' => false, // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Audio_Sanitizer::class             => new AMP_Audio_Sanitizer(
				$dom,
				[
					'native_audio_used' => false, // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Iframe_Sanitizer::class            => new AMP_Iframe_Sanitizer(
				$dom,
				[
					'native_iframe_used' => false, // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Style_Sanitizer::class             => new AMP_Style_Sanitizer(
				$dom,
				[
					'use_document_element'     => true,
					'disable_style_processing' => false, // Overridden by AMP_Script_Sanitizer when there is a kept script.
				]
			),
			AMP_Tag_And_Attribute_Sanitizer::class => new AMP_Tag_And_Attribute_Sanitizer(
				$dom,
				[
					'use_document_element' => true,
				]
			),
		];

		/** @var AMP_Base_Sanitizer $sanitizer */
		foreach ( $sanitizers as $sanitizer ) {
			$sanitizer->init( $sanitizers );
		}

		foreach ( $sanitizers as $sanitizer ) {
			$sanitizer->sanitize();
		}

		$style_element_query = $dom->xpath->query( '//style' );
		$link_element_query  = $dom->xpath->query( '//link[ @rel = "stylesheet" ]' );
		$first_style         = $style_element_query->item( 0 );
		$this->assertEquals( $level > 1, $first_style->hasAttribute( Attribute::AMP_CUSTOM ) );
		$this->assertEquals( 1 === $level ? 2 : 1, $style_element_query->length, 'Expected styles to be concatenated into one style.' );
		$this->assertEquals( 1 === $level ? 2 : 0, $link_element_query->length, 'Expected external stylesheets to be left as-is in Level 1 only.' );

		$css_text = join(
			'',
			array_map(
				function ( Element $element ) {
					return $element->textContent;
				},
				iterator_to_array( $style_element_query )
			)
		);

		foreach ( $style_element_query as $style_element ) {
			$this->assertEquals(
				3 !== $level,
				ValidationExemption::is_px_verified_for_node( $style_element )
			);
		}
		foreach ( $link_element_query as $link_element ) {
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $link_element ) );
		}

		$this->assertEquals(
			3 === $level ? 0 : 1,
			$dom->getElementsByTagName( Tag::SCRIPT )->length
		);

		if ( 1 === $level ) {
			$this->assertStringContainsString( '/*comment*/', $css_text );
		} else {
			$this->assertStringNotContainsString( '/*comment*/', $css_text );
		}
		if ( $level > 1 ) {
			$this->assertStringContainsString( 'head{background-color:white}', $css_text );
			$this->assertStringContainsString( 'body{background-color:black}', $css_text );
		} else {
			$this->assertStringNotContainsString( 'head{background-color:white}', $css_text );
			$this->assertStringNotContainsString( 'body{background-color:black}', $css_text );
		}

		$this->assertEquals(
			1 === $level ? 0 : 1,
			$dom->getElementsByTagName( Extension::IMG )->length,
			'Expected IMG to be converted to AMP-IMG when custom scripts are removed.'
		);
		$this->assertMatchesRegularExpression(
			1 === $level ? '/}\s*img\s*{/' : '/}amp-img{/',
			$css_text
		);

		$this->assertEquals(
			1 === $level ? 0 : 1,
			$dom->getElementsByTagName( Extension::VIDEO )->length,
			'Expected VIDEO to be converted to AMP-VIDEO when custom scripts are removed.'
		);
		$this->assertMatchesRegularExpression(
			1 === $level ? '/}\s*video\s*{/' : '/}amp-video{/',
			$css_text
		);

		$this->assertEquals(
			1 !== $level ? 1 : 0,
			$dom->getElementsByTagName( Extension::AUDIO )->length,
			'Expected AUDIO to be converted to AMP-AUDIO when custom scripts are removed.'
		);

		switch ( $level ) {
			case 1:
				$this->assertMatchesRegularExpression( '/}\s*audio\s*{\s*outline:\s*solid 1px green !important/', $css_text );
				break;
			case 2:
				$this->assertStringContainsString( '}amp-audio{', $css_text );
				break;
			case 3:
				$this->assertStringContainsString( '}amp-audio:not(#_', $css_text );
				break;
		}

		$blueviolet_div = $dom->getElementById( 'blueviolet' );
		if ( $level < 3 ) {
			$this->assertSame( 'color:blueviolet !important;', $blueviolet_div->getAttribute( 'style' ), 'Expected style attribute to be left as-is in level under 3.' );
			$this->assertStringNotContainsString( 'blueviolet', $css_text, 'Expected blueviolet not to be concatenated under level 3.' );
		} else {
			$this->assertFalse( $blueviolet_div->hasAttribute( 'style' ), 'Expected no style attribute in level 3.' );
			$this->assertStringContainsString( 'blueviolet', $css_text, 'Expected ' );
		}

		$this->assertEquals(
			1 === $level ? 0 : 1,
			$dom->getElementsByTagName( Extension::IFRAME )->length,
			'Expected IFRAME to be converted to AMP-IFRAME when custom scripts are removed.'
		);
		$this->assertMatchesRegularExpression(
			1 === $level ? '/}\s*iframe\s*{/' : '/}amp-iframe{/',
			$css_text
		);

		$post_form = $dom->xpath->query( '//form[ @method = "post" ]' )->item( 0 );
		$this->assertInstanceOf( Element::class, $post_form );
		$this->assertEquals( 3 === $level, $post_form->hasAttribute( Attribute::ACTION_XHR ) );
		$this->assertEquals( 3 !== $level, $post_form->hasAttribute( Attribute::ACTION ) );

		if ( 1 === $level ) {
			$this->assertStringContainsString( 'body.loaded', $css_text );
		} else {
			$this->assertStringNotContainsString( 'body.loaded', $css_text );
		}
		if ( 3 === $level ) {
			$this->assertStringNotContainsString( 'body:after{', $css_text );
		} else {
			$this->assertStringContainsString( 'body:after{', $css_text );
		}

		// Get registered scripts.
		$scripts = $sanitizers[ AMP_Tag_And_Attribute_Sanitizer::class ]->get_scripts();
		$this->assertArrayHasKey( Extension::FACEBOOK_PAGE, $scripts );
		$this->assertArrayNotHasKey( Extension::FACEBOOK, $scripts );
	}

	/** @return array */
	public function get_comment_reply_allowed_values() {
		$values = [
			'always',
			'conditionally',
			'never',
		];

		$data = [];
		foreach ( $values as $value ) {
			$data[ $value ] = [ 'comment_reply_allowed' => $value ];
		}
		return $data;
	}

	/**
	 * @dataProvider get_comment_reply_allowed_values
	 * @param string $comment_reply_allowed
	 * @covers ::sanitize_js_script_elements()
	 */
	public function test_sanitize_js_script_elements_for_comment_reply( $comment_reply_allowed ) {
		$dom = Document::fromHtml(
			'
			<html>
				<head></head>
				<body>
					<script id="comment-reply-js" src="https://example.com/comment-reply.js"></script>
				</body>
			</html>
			',
			Options::DEFAULTS
		);

		$sanitizer = new AMP_Script_Sanitizer(
			$dom,
			[
				'sanitize_js_scripts'   => true,
				'comment_reply_allowed' => $comment_reply_allowed,
			]
		);

		$script = $dom->getElementById( 'comment-reply-js' );
		$this->assertInstanceOf( Element::class, $script );
		$sanitizer->sanitize();

		if ( 'always' === $comment_reply_allowed ) {
			$this->assertInstanceOf( Element::class, $script->parentNode );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		} elseif ( 'never' === $comment_reply_allowed ) {
			$this->assertNull( $script->parentNode );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		} else {
			$this->assertInstanceOf( Element::class, $script->parentNode );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * @see wp_comment_form_unfiltered_html_nonce()
	 * @covers ::sanitize_js_script_elements()
	 */
	public function test_sanitize_js_script_elements_for_wp_unfiltered_html_comment_script() {
		add_filter(
			'map_meta_cap',
			static function ( $caps, $cap ) {
				if ( 'unfiltered_html' === $cap ) {
					$caps = [ 'exist' ];
				}
				return $caps;
			},
			10,
			2
		);
		$this->assertTrue( current_user_can( 'unfiltered_html' ) );

		$dom = Document::fromHtml(
			'
			<html data-ampdevmode>
				<head></head>
				<body>' . get_echo( 'wp_comment_form_unfiltered_html_nonce' ) . '</body>
			</html>
			',
			Options::DEFAULTS
		);

		$sanitizer = new AMP_Script_Sanitizer(
			$dom,
			[
				'sanitize_js_scripts' => true,
			]
		);

		$sanitizer->sanitize();

		$script = $dom->getElementsByTagName( Tag::SCRIPT )->item( 0 );
		$this->assertInstanceOf( Element::class, $script );
		$this->assertStringContainsString( '_wp_unfiltered_html_comment_disabled', $script->textContent );
		$this->assertTrue( DevMode::hasExemptionForNode( $script ) );
	}

	/**
	 * Test style[amp-boilerplate] preservation.
	 */
	public function test_boilerplate_preservation() {
		ob_start();
		?>
		<!doctype html>
		<html amp>
			<head>
				<meta charset="utf-8">
				<link rel="canonical" href="self.html" />
				<meta name="viewport" content="width=device-width,minimum-scale=1">
				<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
				<script async src="https://cdn.ampproject.org/v0.js"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>

				<!-- Google Tag Manager -->
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','GTM-XXXX');</script>
				<!-- End Google Tag Manager -->
			</head>
			<body>
				<!-- Google Tag Manager (noscript) -->
				<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-XXXX"
				height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<!-- End Google Tag Manager (noscript) -->

				Hello, AMP world.
				Has script? <script>document.write("Yep!")</script><noscript>Nope!</noscript>
			</body>
		</html>
		<?php
		$html = ob_get_clean();
		$args = [
			'use_document_element' => true,
		];

		$dom = Document::fromHtml( $html, Options::DEFAULTS );
		AMP_Content_Sanitizer::sanitize_document( $dom, amp_get_content_sanitizers(), $args );

		$content = $dom->saveHTML( $dom->documentElement );

		$this->assertMatchesRegularExpression( '/<!-- Google Tag Manager -->\s*<!-- End Google Tag Manager -->/', $content );
		$this->assertStringContainsString( '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>', $content );
		$this->assertStringContainsString( 'Has script? <!--noscript-->Nope!<!--/noscript-->', $content );
		$this->assertStringContainsString( '<!--noscript--><amp-iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" data-amp-original-style="display:none;visibility:hidden" class="amp-wp-b3bfe1b"><span placeholder="" class="amp-wp-iframe-placeholder"></span><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="0" width="0"></iframe></noscript></amp-iframe><!--/noscript-->', $content );
	}
}
