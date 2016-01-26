<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

class AMP_Audio_Sanitizer extends AMP_Base_Sanitizer {
	public static $tag = 'audio';

	private static $script_slug = 'amp-audio';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-audio-0.1.js';

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}

	public function sanitize() {
		$nodes = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			$new_attributes = $this->filter_attributes( $old_attributes );

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-audio', $new_attributes );

			// TODO: limit child nodes too (only allowed: `source`; move rest to div+fallback)
			// TODO: `source` does not have closing tag, and DOMDocument doesn't handle it well.
			foreach ( $node->childNodes as $child_node ) {
				$new_child_node = $child_node->cloneNode( true );
				$new_node->appendChild( $new_child_node );
			}

			$node->parentNode->replaceChild( $new_node, $node );

			$this->did_convert_elements = true;
		}
	}

	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
				case 'width':
				case 'height':
				case 'class':
					$out[ $name ] = $value;
					break;
				case 'loop':
				case 'muted':
					if ( 'false' !== $value ) {
						$out[ $name ] = 'true';
					}
					break;
				case 'autoplay':
					$out[ $name ] = 'desktop tablet mobile';
					break;
				default;
					break;
			}
		}

		return $out;
	}
}
