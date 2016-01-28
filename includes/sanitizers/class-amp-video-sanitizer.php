<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

/**
 * Converts <video> tags to <amp-video>
 */
class AMP_Video_Sanitizer extends AMP_Base_Sanitizer {
	const FALLBACK_HEIGHT = 400;

	public static $tag = 'video';

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
			if ( ! isset( $new_attributes['width'], $new_attributes['height'] ) ) {
				$new_attributes['height'] = self::FALLBACK_HEIGHT;
				$new_attributes['layout'] = 'fixed-height';
			}
			$new_attributes = $this->enforce_sizes_attribute( $new_attributes );

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-video', $new_attributes );

			// TODO: limit child nodes too (only allowed: `source`; move rest to div+fallback)
			// TODO: `source` does not have closing tag, and DOMDocument doesn't handle it well.
			foreach ( $node->childNodes as $child_node ) {
				$new_child_node = $child_node->cloneNode( true );
				$new_node->appendChild( $new_child_node );
			}

			$node->parentNode->replaceChild( $new_node, $node );
		}
	}

	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
				case 'poster':
				case 'width':
				case 'height':
				case 'class':
				case 'sizes':
					$out[ $name ] = $value;
					break;
				case 'controls':
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
