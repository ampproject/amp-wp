<?php
/**
 * Class AMP_DOM_Utils.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\Tag;

/**
 * Class AMP_DOM_Utils
 *
 * Functionality to simplify working with Dom\Documents and DOMElements.
 */
class AMP_DOM_Utils {

	/**
	 * Attribute prefix for AMP-bind data attributes.
	 *
	 * @since 1.2.1
	 * @deprecated Use AmpProject\Dom\Document::AMP_BIND_DATA_ATTR_PREFIX instead.
	 * @var string
	 */
	const AMP_BIND_DATA_ATTR_PREFIX = Document::AMP_BIND_DATA_ATTR_PREFIX;

	/**
	 * Regular expression pattern to match events and actions within an 'on' attribute.
	 *
	 * @since 1.4.0
	 * @var string
	 */
	const AMP_EVENT_ACTIONS_REGEX_PATTERN = '/((?<event>[^:;]+):(?<actions>(?:[^;,\(]+(?:\([^\)]+\))?,?)+))+?/';

	/**
	 * Regular expression pattern to match individual actions within an event.
	 *
	 * @since 1.4.0
	 * @var string
	 */
	const AMP_ACTION_REGEX_PATTERN = '/(?<action>[^(),\s]+(?:\([^\)]+\))?)+/';

	/**
	 * Regular expression pattern to match the contents of the <body> element.
	 *
	 * @since 1.5.0
	 * @var string
	 */
	const HTML_BODY_CONTENTS_PATTERN = '#^.*?<body.*?>(.*)</body>.*?$#si';

	/**
	 * Return a valid Dom\Document representing HTML document passed as a parameter.
	 *
	 * @since 0.7
	 * @see AMP_DOM_Utils::get_content_from_dom_node()
	 * @codeCoverageIgnore
	 * @deprecated Use AmpProject\Dom\Document::fromHtml( $html, $encoding ) instead.
	 *
	 * @param string $document Valid HTML document to be represented by a Dom\Document.
	 * @param string $encoding Optional. Encoding to use for the content.
	 * @return Document|false Returns Dom\Document, or false if conversion failed.
	 */
	public static function get_dom( $document, $encoding = null ) {
		_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document::fromHtml()' );
		return Document::fromHtml( $document, $encoding );
	}

	/**
	 * Determine whether a node can be in the head.
	 *
	 * @link https://github.com/ampproject/amphtml/blob/445d6e3be8a5063e2738c6f90fdcd57f2b6208be/validator/engine/htmlparser.js#L83-L100
	 * @link https://www.w3.org/TR/html5/document-metadata.html
	 * @codeCoverageIgnore
	 * @deprecated Use AmpProject\Dom\Document->isValidHeadNode() instead.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether valid head node.
	 */
	public static function is_valid_head_node( DOMNode $node ) {
		_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document->isValidHeadNode()' );
		return Document::fromNode( $node )->isValidHeadNode( $node );
	}

	/**
	 * Get attribute prefix for converted amp-bind attributes.
	 *
	 * This contains a random string to prevent HTML content containing this data- attribute
	 * originally from being mutated to contain an amp-bind attribute when attributes are restored.
	 *
	 * @since 0.7
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @see \AMP_DOM_Utils::restore_amp_bind_attributes()
	 * @codeCoverageIgnore
	 * @deprecated Use AmpProject\Dom\Document::AMP_BIND_DATA_ATTR_PREFIX instead.
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @return string HTML5 data-* attribute name prefix for AMP binding attributes.
	 */
	public static function get_amp_bind_placeholder_prefix() {
		_deprecated_function( __METHOD__, '1.2.1' );
		return Document::AMP_BIND_DATA_ATTR_PREFIX;
	}

	/**
	 * Replace AMP binding attributes with something that libxml can parse (as HTML5 data-* attributes).
	 *
	 * This is necessary because attributes in square brackets are not understood in PHP and
	 * get dropped with an error raised:
	 * > Warning: DOMDocument::loadHTML(): error parsing attribute name
	 * This is a reciprocal function of AMP_DOM_Utils::restore_amp_bind_attributes().
	 *
	 * @since 0.7
	 * @codeCoverageIgnore
	 * @deprecated This is handled automatically via AmpProject\Dom\Document.
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @param string $html HTML containing amp-bind attributes.
	 * @return string HTML with AMP binding attributes replaced with HTML5 data-* attributes.
	 */
	public static function convert_amp_bind_attributes( $html ) {
		_deprecated_function( __METHOD__, '1.5.0' );
		return $html;
	}

	/**
	 * Convert AMP bind-attributes back to their original syntax.
	 *
	 * This is a reciprocal function of AMP_DOM_Utils::convert_amp_bind_attributes().
	 *
	 * @since 0.7
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @codeCoverageIgnore
	 * @deprecated This is handled automatically via AmpProject\Dom\Document.
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @param string $html HTML with amp-bind attributes converted.
	 * @return string HTML with amp-bind attributes restored.
	 */
	public static function restore_amp_bind_attributes( $html ) {
		_deprecated_function( __METHOD__, '1.2.1' );
		return $html;
	}

	/**
	 * Return a valid Dom\Document representing arbitrary HTML content passed as a parameter.
	 *
	 * @see Reciprocal function get_content_from_dom()
	 *
	 * @since 0.2
	 *
	 * @param string $content  Valid HTML content to be represented by a Dom\Document.
	 * @param string $encoding Optional. Encoding to use for the content. Defaults to `get_bloginfo( 'charset' )`.
	 *
	 * @return Document|false Returns a DOM document, or false if conversion failed.
	 */
	public static function get_dom_from_content( $content, $encoding = null ) {
		// Detect encoding from the current WordPress installation.
		if ( null === $encoding ) {
			$encoding = get_bloginfo( 'charset' );
		}

		/*
		 * Wrap in dummy tags, since XML needs one parent node.
		 * It also makes it easier to loop through nodes.
		 * We can later use this to extract our nodes.
		 */
		$document = "<html><head></head><body>{$content}</body></html>";

		return Document::fromHtml( $document, $encoding );
	}

	/**
	 * Return valid HTML *body* content extracted from the Dom\Document passed as a parameter.
	 *
	 * @since 0.2
	 * @see AMP_DOM_Utils::get_content_from_dom_node() Reciprocal function.
	 *
	 * @param Document $dom Represents an HTML document from which to extract HTML content.
	 * @return string Returns the HTML content of the body element represented in the Dom\Document.
	 */
	public static function get_content_from_dom( Document $dom ) {
		return preg_replace(
			static::HTML_BODY_CONTENTS_PATTERN,
			'$1',
			$dom->saveHTML( $dom->body )
		);
	}

	/**
	 * Return valid HTML content extracted from the DOMNode passed as a parameter.
	 *
	 * @since 0.6
	 * @see AMP_DOM_Utils::get_dom() Where the operations in this method are mirrored.
	 * @see AMP_DOM_Utils::get_content_from_dom() Reciprocal function.
	 * @codeCoverageIgnore
	 * @deprecated Use Dom\Document->saveHtml( $node ) instead.
	 *
	 * @param Document   $dom  Represents an HTML document.
	 * @param DOMElement $node Represents an HTML element of the $dom from which to extract HTML content.
	 * @return string Returns the HTML content represented in the DOMNode
	 */
	public static function get_content_from_dom_node( Document $dom, $node ) {
		_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document::saveHtml()' );
		return $dom->saveHTML( $node );
	}

	/**
	 * Create a new node w/attributes (a DOMElement) and add to the passed Dom\Document.
	 *
	 * @since 0.2
	 *
	 * @param Document $dom        A representation of an HTML document to add the new node to.
	 * @param string   $tag        A valid HTML element tag for the element to be added.
	 * @param string[] $attributes One of more valid attributes for the new node.
	 *
	 * @return DOMElement|false The DOMElement for the given $tag, or false on failure
	 */
	public static function create_node( Document $dom, $tag, $attributes ) {
		$node = $dom->createElement( $tag );
		self::add_attributes_to_node( $node, $attributes );

		return $node;
	}

	/**
	 * Extract a DOMElement node's HTML element attributes and return as an array.
	 *
	 * @since 0.2
	 *
	 * @param DOMElement $node Represents an HTML element for which to extract attributes.
	 *
	 * @return string[] The attributes for the passed node, or an
	 *                  empty array if it has no attributes.
	 */
	public static function get_node_attributes_as_assoc_array( $node ) {
		$attributes = [];
		if ( ! $node->hasAttributes() ) {
			return $attributes;
		}

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		return $attributes;
	}

	/**
	 * Add one or more HTML element attributes to a node's DOMElement.
	 *
	 * @since 0.2
	 *
	 * @param DOMElement $node       Represents an HTML element.
	 * @param string[]   $attributes One or more attributes for the node's HTML element.
	 */
	public static function add_attributes_to_node( $node, $attributes ) {
		foreach ( $attributes as $name => $value ) {
			try {
				$node->setAttribute( $name, $value );
			} catch ( DOMException $e ) {
				/*
				 * Catch a "Invalid Character Error" when libxml is able to parse attributes with invalid characters,
				 * but it throws error when attempting to set them via DOM methods. For example, '...this' can be parsed
				 * as an attribute but it will throw an exception when attempting to setAttribute().
				 */
				continue;
			}
		}
	}

	/**
	 * Determines if a DOMElement's node is empty or not..
	 *
	 * @since 0.2
	 *
	 * @param DOMElement $node Represents an HTML element.
	 * @return bool Returns true if the DOMElement has no child nodes and
	 *              the textContent property of the DOMElement is empty;
	 *              Otherwise it returns false.
	 */
	public static function is_node_empty( $node ) {
		return false === $node->hasChildNodes() && empty( $node->textContent );
	}

	/**
	 * Forces HTML element closing tags given a Dom\Document and optional DOMElement
	 *
	 * @since 0.2
	 * @codeCoverageIgnore
	 * @deprecated
	 *
	 * @param Document   $dom  Represents HTML document on which to force closing tags.
	 * @param DOMElement $node Represents HTML element to start closing tags on.
	 *                         If not passed, defaults to first child of body.
	 */
	public static function recursive_force_closing_tags( $dom, $node = null ) {
		_deprecated_function( __METHOD__, '0.7' );

		if ( null === $node ) {
			$node = $dom->body;
		}

		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			return;
		}

		if ( self::is_self_closing_tag( $node->nodeName ) ) {
			/*
			 * Ensure there is no text content to accidentally force a child
			 */
			$node->textContent = null;
			return;
		}

		if ( self::is_node_empty( $node ) ) {
			$text_node = $dom->createTextNode( '' );
			$node->appendChild( $text_node );

			return;
		}

		$num_children = $node->childNodes->length;
		for ( $i = $num_children - 1; $i >= 0; $i -- ) {
			$child = $node->childNodes->item( $i );
			self::recursive_force_closing_tags( $dom, $child );
		}

	}

	/**
	 * Determines if an HTML element tag is validly a self-closing tag per W3C HTML5 specs.
	 *
	 * @since 0.2
	 *
	 * @param string $tag Tag.
	 * @return bool Returns true if a valid self-closing tag, false if not.
	 */
	private static function is_self_closing_tag( $tag ) {
		return in_array( strtolower( $tag ), Tag::SELF_CLOSING_TAGS, true );
	}

	/**
	 * Check whether a given element has a specific class.
	 *
	 * @since 1.4.0
	 *
	 * @param DOMElement $element Element to check the classes of.
	 * @param string     $class   Class to check for.
	 * @return bool Whether the element has the requested class.
	 */
	public static function has_class( DOMElement $element, $class ) {
		if ( ! $element->hasAttribute( 'class' ) ) {
			return false;
		}

		$classes = $element->getAttribute( 'class' );

		return in_array( $class, preg_split( '/\s/', $classes ), true );
	}

	/**
	 * Get the ID for an element.
	 *
	 * If the element does not have an ID, create one first.
	 *
	 * @since 1.4.0
	 *
	 * @param DOMElement $element Element to get the ID for.
	 * @param string     $prefix  Optional. Defaults to '_amp_wp_id_'.
	 * @return string ID to use.
	 */
	public static function get_element_id( $element, $prefix = 'amp-wp-id' ) {
		static $index_counter = [];

		if ( $element->hasAttribute( 'id' ) ) {
			return $element->getAttribute( 'id' );
		}

		if ( ! array_key_exists( $prefix, $index_counter ) ) {
			$index_counter[ $prefix ] = 2;
			$element->setAttribute( 'id', $prefix );

			return $prefix;
		}

		$id = "{$prefix}-{$index_counter[ $prefix ]}";
		$index_counter[ $prefix ] ++;

		$element->setAttribute( 'id', $id );

		return $id;
	}

	/**
	 * Register an AMP action to an event on a given element.
	 *
	 * If the element already contains one or more events or actions, the method
	 * will assemble them in a smart way.
	 *
	 * @since 1.4.0
	 *
	 * @param DOMElement $element Element to add an action to.
	 * @param string     $event   Event to trigger the action on.
	 * @param string     $action  Action to add.
	 */
	public static function add_amp_action( DOMElement $element, $event, $action ) {
		$event_action_string = "{$event}:{$action}";

		if ( ! $element->hasAttribute( 'on' ) ) {
			// There's no "on" attribute yet, so just add it and be done.
			$element->setAttribute( 'on', $event_action_string );
			return;
		}

		$element->setAttribute(
			'on',
			self::merge_amp_actions(
				$element->getAttribute( 'on' ),
				$event_action_string
			)
		);
	}

	/**
	 * Merge two sets of AMP events & actions.
	 *
	 * @since 1.4.0
	 *
	 * @param string $first  First event/action string.
	 * @param string $second First event/action string.
	 * @return string Merged event/action string.
	 */
	public static function merge_amp_actions( $first, $second ) {
		$events = [];
		foreach ( [ $first, $second ] as $event_action_string ) {
			$matches = [];
			$results = preg_match_all( self::AMP_EVENT_ACTIONS_REGEX_PATTERN, $event_action_string, $matches );

			if ( ! $results || ! isset( $matches['event'] ) ) {
				continue;
			}

			foreach ( $matches['event'] as $index => $event ) {
				$events[ $event ][] = $matches['actions'][ $index ];
			}
		}

		$value_strings = [];
		foreach ( $events as $event => $action_strings_array ) {
			$actions_array = [];
			array_walk(
				$action_strings_array,
				static function ( $actions ) use ( &$actions_array ) {
					$matches = [];
					$results = preg_match_all( self::AMP_ACTION_REGEX_PATTERN, $actions, $matches );

					if ( ! $results || ! isset( $matches['action'] ) ) {
						$actions_array[] = $actions;
						return;
					}

					$actions_array = array_merge( $actions_array, $matches['action'] );
				}
			);

			$actions         = implode( ',', array_unique( array_filter( $actions_array ) ) );
			$value_strings[] = "{$event}:{$actions}";
		}

		return implode( ';', $value_strings );
	}

	/**
	 * Copy one or more attributes from one element to the other.
	 *
	 * @param array|string $attributes        Attribute name or array of attribute names to copy.
	 * @param DOMElement   $from              DOM element to copy the attributes from.
	 * @param DOMElement   $to                DOM element to copy the attributes to.
	 * @param string       $default_separator Default separator to use for multiple values if the attribute is not known.
	 */
	public static function copy_attributes( $attributes, DOMElement $from, DOMElement $to, $default_separator = ',' ) {
		foreach ( (array) $attributes as $attribute ) {
			if ( $from->hasAttribute( $attribute ) ) {
				$values = $from->getAttribute( $attribute );
				if ( $to->hasAttribute( $attribute ) ) {
					switch ( $attribute ) {
						case 'on':
							$values = self::merge_amp_actions( $to->getAttribute( $attribute ), $values );
							break;
						case 'class':
							$values = $to->getAttribute( $attribute ) . ' ' . $values;
							break;
						default:
							$values = $to->getAttribute( $attribute ) . $default_separator . $values;
					}
				}
				$to->setAttribute( $attribute, $values );
			}
		}
	}
}
