<?php

use AmpProject\AmpWP\Dom\Options;
use AmpProject\Dom\Document;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Class AMP_DOM_Utils_Test
 *
 * These are here because PhpStorm cannot find them because of phpunit6-compat.php
 *
 * @method void assertEquals( mixed $expected, mixed $actual, string $errorMessage=null )
 * @method void assertTrue( bool $expectsTrue, string $errorMessage=null )
 * @method void assertFalse( bool $expectsFalse, string $errorMessage=null )
 */
class AMP_DOM_Utils_Test extends TestCase {

	/**
	 * Test UTF-8 content.
	 *
	 * @covers AMP_DOM_Utils::get_dom_from_content()
	 * @covers AMP_DOM_Utils::get_content_from_dom()
	 */
	public function test_utf8_content() {
		$source   = '<p>Iñtërnâtiônàlizætiøn</p>';
		$expected = '<p>Iñtërnâtiônàlizætiøn</p>';

		$dom     = AMP_DOM_Utils::get_dom_from_content( $source );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test adding no attributes to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__no_attributes() {
		$dom  = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node = $dom->createElement( 'b' );
		AMP_DOM_Utils::add_attributes_to_node( $node, [] );
		$this->assertFalse( $node->hasAttributes() );
	}

	/**
	 * Test adding attribute with no value to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__attribute_without_value() {
		$dom        = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node       = $dom->createElement( 'div' );
		$attributes = [ 'placeholder' => '' ];
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	/**
	 * Test adding attribute with value to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__attribute_with_value() {
		$dom        = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node       = $dom->createElement( 'div' );
		$attributes = [
			'class' => 'myClass',
			'id'    => 'myId',
		];
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	/**
	 * Assert node has the expected attributes.
	 *
	 * @param DOMElement $node        Element.
	 * @param string[]   $attributes Attributes.
	 */
	protected function check_node_has_attributes( $node, $attributes ) {
		$this->assertEquals( count( $attributes ), $node->attributes->length );
		foreach ( $node->attributes as $attr ) {
			$name  = $attr->nodeName;
			$value = $attr->nodeValue;

			$this->assertTrue( array_key_exists( $name, $attributes ), sprintf( 'Attribute "%s" not found.', $name ) );
			$this->assertEquals( $attributes[ $name ], $value, sprintf( 'Attribute "%s" does not have expected value.', $name ) );
		}
	}

	/**
	 * Test that node is empty.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__yes() {
		$source = '<p></p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertTrue( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that node with text is not empty.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__no__has_text() {
		$source = '<p>Hello</p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that element is not empty when it has a child.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__no__has_child() {
		$source = '<p><b></b></p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that empty tag is parsed and serialized without changes.
	 *
	 * @covers AMP_DOM_Utils::get_dom_from_content()
	 * @covers AMP_DOM_Utils::get_content_from_dom()
	 */
	public function test__get_content_from_dom__br_no_closing_tag() {
		$source   = '<br>';
		$expected = '<br>';

		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test handling of empty elements.
	 *
	 * @covers \AmpProject\Dom\Document::fromHtml()
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 */
	public function test_html5_empty_elements() {
		$original  = '<amp-video width="432" height="987">';
		$original .= '<track kind="subtitles" src="https://example.com/sampleChapters.vtt" srclang="en">';
		$original .= '<source src="foo.webm" type="video/webm">';
		$original .= '<source src="foo.ogg" type="video/ogg" />';
		$original .= '<source src="foo.mpg" type="video/mpeg"></source>';
		$original .= '<div placeholder>Placeholder</div>';
		$original .= '<span fallback>Fallback</span>';
		$original .= '</amp-video>';
		$document  = AMP_DOM_Utils::get_dom_from_content( $original );

		$video = $document->getElementsByTagName( 'amp-video' )->item( 0 );
		$this->assertNotEmpty( $video );
		$this->assertEquals( 6, $video->childNodes->length );
		$this->assertEquals( 'track', $video->childNodes->item( 0 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 1 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 2 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 3 )->nodeName );
		$this->assertEquals( 'div', $video->childNodes->item( 4 )->nodeName );
		$this->assertEquals( 'span', $video->childNodes->item( 5 )->nodeName );
	}

	/**
	 * Test parsing DOM with Mustache or Mustache-like templates.
	 *
	 * @covers \AmpProject\Dom\Document::fromHtml()
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 */
	public function test_mustache_replacements() {

		$data = [
			'foo' => [
				'bar' => [
					'baz' => [],
				],
			],
		];

		$html = implode(
			"\n",
			[
				'<!--amp-source-stack {"block_name":"core\/columns"}-->',
				'<div class="wp-block-columns has-2-columns">',
				'<!--amp-source-stack {"block_name":"core\/quote","block_attrs":{"layout":"column-1"}}-->',
				'<blockquote class="wp-block-quote layout-column-1"><p>Quote</p><cite>Famous</cite></blockquote>',
				'<!--/amp-source-stack {"block_name":"core\/quote","block_attrs":{"layout":"column-1"}}-->',
				'<!-- wp:paragraph -->',
				'<p><a href="https://example.com/"><img src="https://example.com/img.jpg"></a></p>',
				'<!-- /wp:paragraph -->',
				'</div>',
				'<!--/amp-source-stack {"block_name":"core\/columns"}-->',
				'<!-- wp:html {} -->',
				'<script type="application/json">' . wp_json_encode( $data ) . '</script>',
				'<template type="amp-mustache">Hello {{world}}! <a href="{{href}}" title="Hello {{name}}"><img src="{{src}}"></a><blockquote cite="{{cite}}">{{quote}}</blockquote></template>',
				'<!-- /wp:html -->',
			]
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		// Ensure that JSON in scripts are left intact.
		$script = $dom->xpath->query( '//script' )->item( 0 );
		$this->assertEquals(
			$data,
			json_decode( $script->nodeValue, true )
		);

		// Ensure that mustache var in a[href] attribute is intact.
		/**
		 * Elements
		 *
		 * @var DOMElement $template_link
		 * @var DOMElement $template_img
		 * @var DOMElement $template_blockquote
		 */
		$template_link = $dom->xpath->query( '//template/a' )->item( 0 );
		$this->assertSame( '{{href}}', $template_link->getAttribute( 'href' ) );
		$this->assertEquals( 'Hello {{name}}', $template_link->getAttribute( 'title' ) );

		// Ensure that mustache var in img[src] attribute is intact.
		$template_img = $dom->xpath->query( '//template/a/img' )->item( 0 );
		$this->assertEquals( '{{src}}', $template_img->getAttribute( 'src' ) );

		// Ensure that mustache var in blockquote[cite] is not changed.
		$template_blockquote = $dom->xpath->query( '//template/blockquote' )->item( 0 );
		$this->assertEquals( '{{cite}}', $template_blockquote->getAttribute( 'cite' ) );

		$serialized_html = $dom->saveHTML( $dom->documentElement );

		$this->assertStringContainsString( '<a href="{{href}}" title="Hello {{name}}">', $serialized_html );
		$this->assertStringContainsString( '<img src="{{src}}">', $serialized_html );
		$this->assertStringContainsString( '<blockquote cite="{{cite}}">', $serialized_html );
		$this->assertStringContainsString( '"block_attrs":{"layout":"column-1"}}', $serialized_html );
	}

	/**
	 * Test encoding.
	 *
	 * @covers \AmpProject\Dom\Document::fromHtml()
	 */
	public function test_get_dom_encoding() {
		$html  = '<!DOCTYPE html><html><head><title>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</title><meta charset="utf-8"></head><body>';
		$html .= '<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>';
		$html .= '<p>&#x645;&#x631;&#x62D;&#x628;&#x627; &#x628;&#x627;&#x644;&#x639;&#x627;&#x644;&#x645;! Check out &#8216;this&#8217; and &#8220;that&#8221; and&#8212;other things.</p>';
		$html .= '<p>&#x645;&#x631;&#x62D;&#x628;&#x627; &#x628;&#x627;&#x644;&#x639;&#x627;&#x644;&#x645;! Check out &lsquo;this&rsquo; and &ldquo;that&rdquo; and&mdash;other things.</p>';
		$html .= '</body></html>';

		$document = Document::fromHtml( $html, Options::DEFAULTS );

		$this->assertEquals( 'utf-8', $document->encoding );
		$paragraphs = $document->getElementsByTagName( 'p' );
		$this->assertSame( 3, $paragraphs->length );
		$this->assertSame( $paragraphs->item( 0 )->textContent, $paragraphs->item( 1 )->textContent );
		$this->assertSame( $paragraphs->item( 1 )->textContent, $paragraphs->item( 2 )->textContent );
		$this->assertSame( $document->getElementsByTagName( 'title' )->item( 0 )->textContent, $paragraphs->item( 2 )->textContent );
	}

	/**
	 * Test preserving whitespace when serializing Dom\Document as HTML string.
	 *
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 * @covers \AMP_DOM_Utils::get_content_from_dom()
	 * @link https://github.com/ampproject/amp-wp/issues/1304
	 */
	public function test_whitespace_preservation() {
		$body = " start <ul><li>First</li><li>Second</li></ul><style>pre::before { content:'⚡️'; }</style><script type=\"application/json\">\"⚡️\"</script><pre>\t* one\n\t* two\n\t* three</pre> end ";
		$html = "<html><head><meta charset=\"utf-8\"></head><body data-foo=\"&gt;\">$body</body></html>";

		$dom = Document::fromHtml( "<!DOCTYPE html>$html", Options::DEFAULTS );

		$output = $dom->saveHTML( $dom->documentElement );
		$this->assertEquals( $html, $output );

		$output = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $body, $output );
	}

	public function get_has_class_data() {
		$dom = new Document();

		return [
			// Element without class attribute.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [] ), 'target-class', false ],
			// Single class being checked.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'target-class' ] ), 'target-class', true ],
			// Single class not being checked.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'some-class' ] ), 'target-class', false ],
			// Multiple classes with match at the beginning.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'target-class some-class some-other-class something else' ] ), 'target-class', true ],
			// Multiple classes with match in the middle.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'some-class some-other-class target-class something else' ] ), 'target-class', true ],
			// Multiple classes with match at the end.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'some-class some-other-class something else target-class' ] ), 'target-class', true ],
			// Multiple classes with match and random whitespace.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => '  some-class    some-other-class   target-class   something   else   target-class  ' ] ), 'target-class', true ],
			// Multiple classes without match.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'some-class some-other-class something else' ] ), 'target-class', false ],
			// Single class with UTF-8.
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'class' => 'some-class Iñtërnâtiônàlizætiøn some-other-class' ] ), 'Iñtërnâtiônàlizætiøn', true ],
			// Target class in other attribute
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'some-attribute' => 'target-class' ] ), 'target-class', false ],
		];
	}

	/**
	 * Test has_class().
	 *
	 * @dataProvider get_has_class_data
	 * @covers \AMP_DOM_Utils::has_class()
	 *
	 * @param DOMElement $element  Element.
	 * @param string     $class    Class names.
	 * @param bool       $expected Expected has class name.
	 */
	public function test_has_class( DOMElement $element, $class, $expected ) {
		$actual = AMP_DOM_Utils::has_class( $element, $class );
		$this->assertEquals( $expected, $actual );
	}

	public function get_get_element_id_data() {
		$element_factory = static function ( $dom, $id = null ) {
			$element = $dom->createElement( 'div' );

			if ( $id ) {
				$element->setAttribute( 'id', $id );
			}

			$dom->body->appendChild( $element );

			return $element;
		};

		return [
			'single check with existing ID'         => [
				[
					[ $element_factory, 'my-id', 'some-prefix', 'my-id' ],
				],
			],

			'single check without existing ID'      => [
				[
					[ $element_factory, null, 'some-prefix', 'some-prefix-0' ],
				],
			],

			'consecutive checks count upwards'      => [
				[
					[ $element_factory, null, 'some-prefix', 'some-prefix-0' ],
					[ $element_factory, null, 'some-prefix', 'some-prefix-1' ],
				],
			],

			'consecutive checks for same element return same ID' => [
				[
					[ $element_factory, null, 'some-prefix', 'some-prefix-0' ],
					[ null, null, 'some-prefix', 'some-prefix-0' ],
				],
			],

			'mixing prefixes keeps counts separate' => [
				[
					[ $element_factory, 'my-id', 'some-prefix', 'my-id' ],
					[ $element_factory, null, 'some-prefix', 'some-prefix-0' ],
					[ $element_factory, null, 'some-prefix', 'some-prefix-1' ],
					[ $element_factory, null, 'other-prefix', 'other-prefix-0' ],
					[ $element_factory, null, 'other-prefix', 'other-prefix-1' ],
					[ $element_factory, null, 'some-prefix', 'some-prefix-2' ],
					[ $element_factory, 'another-id', 'some-prefix', 'another-id' ],
					[ $element_factory, null, 'some-prefix', 'some-prefix-3' ],
					[ null, null, 'some-prefix', 'some-prefix-3' ],
				],
			],
		];
	}

	/**
	 * Test get_element_id().
	 *
	 * @dataProvider get_get_element_id_data
	 * @covers \AMP_DOM_Utils::get_element_id()
	 *
	 * @expectedDeprecated AMP_DOM_Utils::get_element_id
	 *
	 * @param array $checks Checks to perform. Each check is an array containing an element, a prefix and an expected ID.
	 */
	public function test_get_element_id( $checks ) {
		$dom = new Document();
		foreach ( $checks as list( $element_factory, $id, $prefix, $expected ) ) {
			// If no element factory was passed, just reuse the previous element.
			if ( $element_factory ) {
				$element = $element_factory( $dom, $id );
			}

			$actual = AMP_DOM_Utils::get_element_id( $element, $prefix );
			$this->assertEquals( $expected, $actual );
		}
	}

	public function get_add_amp_action_data() {
		$dom    = new Document();
		$button = AMP_DOM_Utils::create_node( $dom, 'button', [] );
		$form   = AMP_DOM_Utils::create_node( $dom, 'form', [] );

		return [
			// Add a toggle class on tap to a button
			[ $button, 'tap', "some-id.toggleClass(class='some-class')", "tap:some-id.toggleClass(class='some-class')" ],
			// Add another toggle class on tap to a button
			[ $button, 'tap', "some-other-id.toggleClass(class='some-class')", "tap:some-id.toggleClass(class='some-class'),some-other-id.toggleClass(class='some-class')" ],
			// Add a third toggle class on tap to a button
			[ $button, 'tap', "third-id.toggleClass(class='some-class')", "tap:some-id.toggleClass(class='some-class'),some-other-id.toggleClass(class='some-class'),third-id.toggleClass(class='some-class')" ],
			// Add some other event to a button
			[ $button, 'event', 'action', "tap:some-id.toggleClass(class='some-class'),some-other-id.toggleClass(class='some-class'),third-id.toggleClass(class='some-class');event:action" ],
			// Add another action to the second event to a button
			[ $button, 'event', 'other-action', "tap:some-id.toggleClass(class='some-class'),some-other-id.toggleClass(class='some-class'),third-id.toggleClass(class='some-class');event:action,other-action" ],
			// Add fourth action to the tap event to a button
			[ $button, 'tap', 'lightbox', "tap:some-id.toggleClass(class='some-class'),some-other-id.toggleClass(class='some-class'),third-id.toggleClass(class='some-class'),lightbox;event:action,other-action" ],
			// Add a submit success action to a form
			[ $form, 'submit-success', 'success-lightbox', 'submit-success:success-lightbox' ],
			// Add a submit error action to a form
			[ $form, 'submit-error', 'error-lightbox', 'submit-success:success-lightbox;submit-error:error-lightbox' ],
			// Make sure separators within methods won't break
			[ AMP_DOM_Utils::create_node( $dom, 'div', [ 'on' => "event:action(method='with problematic characters , : ;')" ] ), 'event', "second-action('with problematic characters , : ;')", "event:action(method='with problematic characters , : ;'),second-action('with problematic characters , : ;')" ],
		];
	}

	/**
	 * Test add_amp_action().
	 *
	 * @dataProvider get_add_amp_action_data
	 * @covers \AMP_DOM_Utils::add_amp_action()
	 *
	 * @param DOMElement $element  Element.
	 * @param string     $event    Event.
	 * @param string     $action   Action.
	 * @param string     $expected Expected.
	 */
	public function test_add_amp_action( DOMElement $element, $event, $action, $expected ) {
		AMP_DOM_Utils::add_amp_action( $element, $event, $action );
		$this->assertEquals( $expected, $element->getAttribute( 'on' ) );
	}

	public function get_merge_amp_actions_data() {
		return [
			// Both empty.
			[ '', '', '' ],
			// First empty.
			[ '', "tap:some-id.toggleClass(class='some-class')", "tap:some-id.toggleClass(class='some-class')" ],
			// Second empty.
			[ "tap:some-id.toggleClass(class='some-class')", '', "tap:some-id.toggleClass(class='some-class')" ],
			// Same event.
			[ "tap:first-id.toggleClass(class='some-class')", "tap:second-id.toggleClass(class='some-class')", "tap:first-id.toggleClass(class='some-class'),second-id.toggleClass(class='some-class')" ],
			// Same event twice.
			[ "tap:first-id.toggleClass(class='some-class'),second-id.toggleClass(class='some-class')", "tap:third-id.toggleClass(class='some-class'),fourth.toggleClass(class='some-class')", "tap:first-id.toggleClass(class='some-class'),second-id.toggleClass(class='some-class'),third-id.toggleClass(class='some-class'),fourth.toggleClass(class='some-class')" ],
			// Different events.
			[ 'submit-success:success-lightbox', 'submit-error:error-lightbox', 'submit-success:success-lightbox;submit-error:error-lightbox' ],
			// Two different events twice.
			[ 'submit-success:success-lightbox;submit-error:error-lightbox', 'submit-success:success-modal;submit-error:error-modal', 'submit-success:success-lightbox,success-modal;submit-error:error-lightbox,error-modal' ],
			// Make sure separators within methods won't break
			[ "event:action(method='with problematic characters , : ;'),second-action('with problematic characters , : ;')", "another-event:another-action(method='with problematic characters , : ;'),second-action('with problematic characters , : ;')", "event:action(method='with problematic characters , : ;'),second-action('with problematic characters , : ;');another-event:another-action(method='with problematic characters , : ;'),second-action('with problematic characters , : ;')" ],
			// Duplicates should be stripped.
			[ 'event:action,other-action,action;other-event:action,other-action,action', 'event:action;other-event:action;event:action', 'event:action,other-action;other-event:action,other-action' ],
		];
	}

	/**
	 * Test merge_amp_actions().
	 *
	 * @dataProvider get_merge_amp_actions_data
	 * @covers \AMP_DOM_Utils::merge_amp_actions()
	 *
	 * @param string $first    First action.
	 * @param string $second   Second action.
	 * @param string $expected Expected merged actions.
	 */
	public function test_merge_amp_actions( $first, $second, $expected ) {
		$actual = AMP_DOM_Utils::merge_amp_actions( $first, $second );
		$this->assertEquals( $expected, $actual );
	}

	public function get_copy_attributes_data() {
		$dom = new Document();

		return [
			// No attributes from full to empty.
			[
				'',
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node( $dom, 'div', [] ),
				[],
			],
			// No attributes from empty to full.
			[
				'',
				AMP_DOM_Utils::create_node( $dom, 'div', [] ),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b',
					'on'             => 'event:action',
					'some-attribute' => 'value-a',
				],
			],
			// No attributes from full to full.
			[
				'',
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b',
					'on'             => 'event:action',
					'some-attribute' => 'value-a',
				],
			],
			// Class attribute from full to full.
			[
				'class',
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b class-c class-d',
					'on'             => 'event:action',
					'some-attribute' => 'value-a',
				],
			],
			// On attribute from full to full.
			[
				'on',
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b',
					'on'             => 'event:action;other-event:other-action',
					'some-attribute' => 'value-a',
				],
			],
			// Other attribute from full to full.
			[
				'some-attribute',
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b',
					'on'             => 'event:action',
					'some-attribute' => 'value-a,value-b',
				],
			],
			// Two attributes from full to full.
			[
				[ 'class', 'on' ],
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-c class-d',
						'on'             => 'other-event:other-action',
						'some-attribute' => 'value-b',
					]
				),
				AMP_DOM_Utils::create_node(
					$dom,
					'div',
					[
						'class'          => 'class-a class-b',
						'on'             => 'event:action',
						'some-attribute' => 'value-a',
					]
				),
				[
					'class'          => 'class-a class-b class-c class-d',
					'on'             => 'event:action;other-event:other-action',
					'some-attribute' => 'value-a',
				],
			],
		];
	}

	/**
	 * Test copy_attributes().
	 *
	 * @dataProvider get_copy_attributes_data
	 * @covers \AMP_DOM_Utils::copy_attributes()
	 *
	 * @param array      $attributes Attributes.
	 * @param DOMElement $from       From element.
	 * @param DOMElement $to         To element.
	 * @param array      $expected   Expected.
	 */
	public function test_copy_attributes( $attributes, DOMElement $from, DOMElement $to, $expected ) {
		AMP_DOM_Utils::copy_attributes( $attributes, $from, $to );
		$this->assertEquals( $expected, AMP_DOM_Utils::get_node_attributes_as_assoc_array( $to ) );
	}
}
