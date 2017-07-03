<?php

// stub classes for AMP_Base_Filter, since it is an abstract class
class AMP_Test_Stub_Filter extends AMP_Base_Filter {
	public function filter() {
		return $this->dom;
	}
}

class AMP_Test_World_Filter extends AMP_Base_Filter {
	public function filter() {
		$node = $this->dom->createElement( 'em' );
		$text = $this->dom->createTextNode( 'World' );
		$node->appendChild( $text );
		$this->dom->getElementsByTagName( 'body' )->item( 0 )->appendChild( $node );
	}

	public function get_scripts() {
		return array( 'scripts' );
	}

	public function get_styles() {
		return array( 'styles' );
	}
}
