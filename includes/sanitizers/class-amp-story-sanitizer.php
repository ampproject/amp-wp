<?php
/**
 * Class AMP_Story_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Story_Sanitizer
 *
 * Sanitizes pages within AMP Stories.
 */
class AMP_Story_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string Figure tag to identify wrapper around AMP elements.
	 * @since 1.0
	 */
	public static $tag = 'amp-story-page';

	/**
	 * Allowed children for given tags.
	 *
	 * @var array
	 */
	private $tag_allowed_children = [
		'amp-story'      => [],
		'amp-story-page' => [],
	];

	/**
	 * Sanitize the AMP elements contained by <amp-story-page> element where necessary.
	 *
	 * @since 0.2
	 */
	public function sanitize() {

		// Gather the allowed children for amp-story and amp-story-page elements.
		foreach ( array_keys( $this->tag_allowed_children ) as $tag_name ) {
			$rule_specs = AMP_Allowed_Tags_Generated::get_allowed_tag( $tag_name );
			if ( ! isset( $rule_specs ) ) {
				continue;
			}
			foreach ( $rule_specs as $rule_spec ) {
				if ( ! isset( $rule_spec['tag_spec']['child_tags']['child_tag_name_oneof'] ) ) {
					continue;
				}
				$this->tag_allowed_children[ $tag_name ] = array_merge(
					$this->tag_allowed_children[ $tag_name ],
					$rule_spec['tag_spec']['child_tags']['child_tag_name_oneof']
				);
			}
		}

		$amp_story_element = $this->dom->getElementsByTagName( 'amp-story' )->item( 0 );
		if ( $amp_story_element instanceof DOMElement ) {
			$this->sanitize_story_element( $amp_story_element );
		}
	}

	/**
	 * Sanitize the children of an amp-story element.
	 *
	 * @param DOMElement $element An amp-story element.
	 */
	private function sanitize_story_element( DOMElement $element ) {
		$page_number = 0;

		$node = $element->firstChild;
		while ( $node ) {
			$next_node = $node->nextSibling;
			if ( $node instanceof DOMElement ) {
				if ( 'amp-story-page' === $node->nodeName ) {
					$page_number++;
					$this->sanitize_story_page_element( $node, $page_number );
				} else {
					$this->maybe_remove_disallowed_child( $node );
				}
			}
			$node = $next_node;
		}
	}

	/**
	 * Sanitize the children of an amp-story-page element.
	 *
	 * @param DOMElement $element     An amp-story-page element.
	 * @param int        $page_number Page number.
	 */
	private function sanitize_story_page_element( DOMElement $element, $page_number ) {
		$cta_layer_count = 0;

		$node = $element->firstChild;
		while ( $node ) {
			$next_node = $node->nextSibling;
			if ( $node instanceof DOMElement ) {
				if ( 'amp-story-cta-layer' === $node->nodeName ) {
					/*
					 * Remove all erroneous Call-to-Action layers.
					 *
					 * Does not use the remove_invalid_child() method
					 * since the withCallToActionValidation HOC in the editor
					 * already warns the user about improper usage.
					 */
					$cta_layer_count ++;
					if ( 1 === $page_number || $cta_layer_count > 1 ) {
						$element->removeChild( $node );
					}
				} else {
					$this->maybe_remove_disallowed_child( $node );
				}
			}
			$node = $next_node;
		}
	}

	/**
	 * Remove an element if it is not allowed by the parent.
	 *
	 * @param DOMElement $node Child element.
	 * @return bool Whether the child was removed.
	 */
	private function maybe_remove_disallowed_child( DOMElement $node ) {
		if ( ! $node->parentNode ) {
			return false;
		}

		$parent_node_name = $node->parentNode->nodeName;
		if ( empty( $this->tag_allowed_children[ $parent_node_name ] ) ) {
			return false;
		}

		if ( in_array( $node->nodeName, $this->tag_allowed_children[ $parent_node_name ], true ) ) {
			return false;
		}

		$this->remove_invalid_child( $node );
		return true;
	}
}
