<?php

// stub class since AMP_Base_Sanitizer is abstract
class AMP_Stub_Sanitizer extends AMP_Base_Sanitizer {
	public function sanitize( $amp_attributes = array() ) {
		return $this->dom;
	}
}

class AMP_Base_Sanitizer_Test extends WP_UnitTestCase {
	public function test_has_tag__no() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$this->assertFalse( $sanitizer->has_tag( 'b' ) );
	}

	public function test_has_tag__yes() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello</p><p>Hello</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$this->assertTrue( $sanitizer->has_tag( 'p' ) );
	}

	public function test_get_tags__none() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$tags = $sanitizer->get_tags( 'p' );
		$this->assertEquals( 0, $tags->length );
	}

	public function test_get_tags__some() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello</p><p>Hello</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$tags = $sanitizer->get_tags( 'p' );
		$this->assertEquals( 2, $tags->length );
	}

	public function test_add_attributes_to__no_attributes() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$node = $dom->createElement( 'b' );
		$sanitizer->add_attributes_to( $node, array() );
		$this->assertFalse( $node->hasAttributes() );
	}

	public function test_add_attributes_to__attribute_without_value() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$node = $dom->createElement( 'div' );
		$attributes = array( 'placeholder' => '' );
		$sanitizer->add_attributes_to( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	public function test_add_attributes_to__attribute_with_value() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$sanitizer = new AMP_Stub_Sanitizer( $dom );
		$node = $dom->createElement( 'div' );
		$attributes = array( 'class' => 'myClass', 'id' => 'myId' );
		$sanitizer->add_attributes_to( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	protected function check_node_has_attributes( $node, $attributes ) {
		$this->assertEquals( count( $attributes ), $node->attributes->length );
		foreach ( $node->attributes as $attr ) {
			$name = $attr->nodeName;
			$value = $attr->nodeValue;

			$this->assertTrue( array_key_exists( $name, $attributes ), sprintf( 'Attribute "%s" not found.', $name ) );
			$this->assertEquals( $attributes[ $name ], $value, sprintf( 'Attribute "%s" does not have expected value.', $name ) );
		}
	}
}
