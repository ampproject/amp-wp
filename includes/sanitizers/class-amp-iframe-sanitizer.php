<?php
/**
 * Class AMP_Iframe_Sanitizer
 *
 * @package AMP
 */

use AmpProject\DevMode;
use AmpProject\Attribute;
use AmpProject\Layout;

/**
 * Class AMP_Iframe_Sanitizer
 *
 * Converts <iframe> tags to <amp-iframe>
 *
 * @internal
 */
class AMP_Iframe_Sanitizer extends AMP_Base_Sanitizer {
	use AMP_Noscript_Fallback;

	/**
	 * Default values for sandboxing IFrame.
	 *
	 * | Sandbox Token                             | Included | Rationale
	 * |-------------------------------------------|----------|----------
	 * | `allow-downloads`                         | Yes      | Useful for downloading documents, etc.
	 * | `allow-downloads-without-user-activation` | No       | Experimental per MDN. Bad UX.
	 * | `allow-forms`                             | Yes      | For embeds like polls.
	 * | `allow-modals`                            | Yes      | For apps to show `confirm()`, etc.
	 * | `allow-orientation-lock`                  | Yes      | Since we `allowfullscreen`, useful for games, etc.
	 * | `allow-pointer-lock`                      | Yes      | Useful for games.
	 * | `allow-popups`                            | Yes      | To open YouTube video in new window, for example.
	 * | `allow-popups-to-escape-sandbox`          | Yes      | Useful for ads.
	 * | `allow-presentation`                      | Yes      | To cast YouTube videos, for example.
	 * | `allow-same-origin`                       | Yes      | Removed if iframe is same origin.
	 * | `allow-scripts`                           | Yes      | An iframe's primary use case is custom JS.
	 * | `allow-storage-access-by-user-activation` | No       | Experimental per MDN.
	 * | `allow-top-navigation`                    | No       | Poor user experience.
	 * | `allow-top-navigation-by-user-activation` | Yes      | Key for clicking `target=_top` links in iframes.
	 *
	 * @since 0.2
	 * @since 2.0.5 Updated to include majority of other sandbox values which are included by default if sandbox is not provided.
	 * @link https://html.spec.whatwg.org/multipage/iframe-embed-object.html#attr-iframe-sandbox
	 *
	 * @const string
	 */
	const SANDBOX_DEFAULTS = 'allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation';

	/**
	 * Tag.
	 *
	 * @since 0.2
	 *
	 * @var string HTML <iframe> tag to identify and replace with AMP version.
	 */
	public static $tag = 'iframe';

	/**
	 * Default args.
	 *
	 * @var array {
	 *     Default args.
	 *
	 *     @type bool   $add_placeholder       Whether to add a placeholder element.
	 *     @type bool   $add_noscript_fallback Whether to add a noscript fallback.
	 *     @type string $current_origin        The current origin serving the page. Normally this will be the $_SERVER[HTTP_HOST].
	 *     @type string $alias_origin          An alternative origin which can be supplied which is used when encountering same-origin iframes.
	 * }
	 */
	protected $DEFAULT_ARGS = [
		'add_placeholder'       => false,
		'add_noscript_fallback' => true,
		'current_origin'        => null,
		'alias_origin'          => null,
	];

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [
			'iframe' => [
				'amp-iframe',
			],
		];
	}

	/**
	 * Sanitize the <iframe> elements from the HTML contained in this instance's Dom\Document.
	 *
	 * @since 0.2
	 */
	public function sanitize() {
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		if ( $this->args['add_noscript_fallback'] ) {
			$this->initialize_noscript_allowed_attributes( self::$tag );
		}

		// Ensure origins are normalized.
		$this->args['current_origin'] = $this->get_origin_from_url( $this->args['current_origin'] );
		if ( ! empty( $this->args['alias_origin'] ) ) {
			$this->args['alias_origin'] = $this->get_origin_from_url( $this->args['alias_origin'] );
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			/**
			 * Iframe element.
			 *
			 * @var DOMElement $node
			 */
			$node = $nodes->item( $i );

			// Skip element if already inside of an AMP element as a noscript fallback, or if it has a dev mode exemption.
			if ( $this->is_inside_amp_noscript( $node ) || DevMode::hasExemptionForNode( $node ) ) {
				continue;
			}

			$normalized_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
			$normalized_attributes = $this->set_layout( $normalized_attributes );
			$normalized_attributes = $this->normalize_attributes( $normalized_attributes );

			/**
			 * If the src doesn't exist, remove the node. Either it never
			 * existed or was invalidated while filtering attributes above.
			 *
			 * @todo: add an arg to allow for a fallback element in this instance (note that filter cannot be used inside a sanitizer).
			 * @see: https://github.com/ampproject/amphtml/issues/2261
			 */
			if ( empty( $normalized_attributes['src'] ) ) {
				$this->remove_invalid_child(
					$node,
					[
						'code'       => AMP_Tag_And_Attribute_Sanitizer::ATTR_REQUIRED_BUT_MISSING,
						'attributes' => [ 'src' ],
						'spec_name'  => 'amp-iframe',
					]
				);
				continue;
			}

			$this->did_convert_elements = true;
			if ( empty( $normalized_attributes[ Attribute::LAYOUT ] ) && ! empty( $normalized_attributes[ Attribute::HEIGHT ] ) && ! empty( $normalized_attributes[ Attribute::WIDTH ] ) ) {

				// Set layout to responsive if the iframe is aligned to full width.
				$figure_node = null;
				if ( $node->parentNode instanceof DOMElement && 'figure' === $node->parentNode->tagName ) {
					$figure_node = $node->parentNode;
				}
				if ( $node->parentNode->parentNode instanceof DOMElement && 'figure' === $node->parentNode->parentNode->tagName ) {
					$figure_node = $node->parentNode->parentNode;
				}

				if (
					! empty( $this->args['align_wide_support'] )
					&& $figure_node
					&& preg_match( '/(^|\s)(alignwide|alignfull)(\s|$)/', $figure_node->getAttribute( Attribute::CLASS_ ) )
				) {
					$normalized_attributes[ Attribute::LAYOUT ] = Layout::RESPONSIVE;
				} else {
					$normalized_attributes[ Attribute::LAYOUT ] = Layout::INTRINSIC;
				}

				$this->add_or_append_attribute( $normalized_attributes, 'class', 'amp-wp-enforced-sizes' );
			}

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-iframe', $normalized_attributes );

			// Find existing placeholder/overflow.
			$placeholder_node = null;
			$overflow_node    = null;
			foreach ( iterator_to_array( $node->childNodes ) as $child ) {
				if ( ! ( $child instanceof DOMElement ) ) {
					continue;
				}
				if ( $child->hasAttribute( 'placeholder' ) ) {
					$placeholder_node = $node->removeChild( $child );
				} elseif ( $child->hasAttribute( 'overflow' ) ) {
					$overflow_node = $node->removeChild( $child );
				}
			}

			// Add placeholder.
			if ( $placeholder_node || true === $this->args['add_placeholder'] ) {
				if ( ! $placeholder_node ) {
					$placeholder_node = $this->build_placeholder(); // @todo Can a better placeholder default be devised?
				}
				$new_node->appendChild( $placeholder_node );
			}

			// Add overflow.
			if ( $new_node->hasAttribute( 'resizable' ) && ! $overflow_node ) {
				$overflow_node = $this->dom->createElement( 'button' );
				$overflow_node->setAttribute( 'overflow', '' );
				if ( $node->hasAttribute( 'data-amp-overflow-text' ) ) {
					$overflow_text = $node->getAttribute( 'data-amp-overflow-text' );
				} else {
					$overflow_text = __( 'Show all', 'amp' );
				}
				$overflow_node->appendChild( $this->dom->createTextNode( $overflow_text ) );
			}
			if ( $overflow_node ) {
				$new_node->appendChild( $overflow_node );
			}

			$node->parentNode->replaceChild( $new_node, $node );

			if ( $this->args['add_noscript_fallback'] ) {
				$node->setAttribute( 'src', $normalized_attributes['src'] );

				// AMP is stricter than HTML5 for this attribute, so make sure we use a normalized value.
				if ( $node->hasAttribute( 'frameborder' ) ) {
					$node->setAttribute( 'frameborder', $normalized_attributes['frameborder'] );
				}

				// Preserve original node in noscript for no-JS environments.
				$this->append_old_node_noscript( $new_node, $node, $this->dom );
			}
		}
	}

	/**
	 * Normalize HTML attributes for <amp-iframe> elements.
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type string $src IFrame URL - Empty if HTTPS required per $this->args['require_https_src']
	 *      @type int $width <iframe> width attribute - Set to numeric value if px or %
	 *      @type int $height <iframe> height attribute - Set to numeric value if px or %
	 *      @type string $sandbox <iframe> `sandbox` attribute - Pass along if found; default to value of self::SANDBOX_DEFAULTS
	 *      @type string $class <iframe> `class` attribute - Pass along if found
	 *      @type string $sizes <iframe> `sizes` attribute - Pass along if found
	 *      @type string $id <iframe> `id` attribute - Pass along if found
	 *      @type int $frameborder <iframe> `frameborder` attribute - Filter to '0' or '1'; default to '0'
	 *      @type bool $allowfullscreen <iframe> `allowfullscreen` attribute - Convert 'false' to empty string ''
	 *      @type bool $allowtransparency <iframe> `allowtransparency` attribute - Convert 'false' to empty string ''
	 *      @type string $type <iframe> `type` attribute - Pass along if value is not `text/html`
	 * }
	 * @return array Returns HTML attributes; normalizes src, dimensions, frameborder, sandbox, allowtransparency and allowfullscreen
	 */
	private function normalize_attributes( $attributes ) {
		$out = [];

		$remove_allow_same_origin = false;
		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
					// Make the URL absolute since relative URLs are not allowed in amp-iframe.
					if ( '/' === substr( $value, 0, 1 ) && '/' !== substr( $value, 1, 1 ) ) {
						$value = untrailingslashit( $this->args['current_origin'] ) . $value;
					}

					$value = $this->maybe_enforce_https_src( $value, true );

					// Handle case where iframe source origin is the same as the host page's origin.
					if ( $this->get_origin_from_url( $value ) === $this->args['current_origin'] ) {
						if ( ! empty( $this->args['alias_origin'] ) ) {
							$value = preg_replace( '#^\w+://[^/]+#', $this->args['alias_origin'], $value );
						} else {
							$remove_allow_same_origin = true;
						}
					}

					$out[ $name ] = $value;
					break;

				case 'width':
				case 'height':
					$out[ $name ] = $this->sanitize_dimension( $value, $name );
					break;

				case 'frameborder':
					$out[ $name ] = $this->sanitize_boolean_digit( $value );
					break;

				case 'allowfullscreen':
				case 'allowtransparency':
					if ( 'false' !== strtolower( $value ) ) {
						$out[ $name ] = '';
					}
					break;

				case 'mozallowfullscreen':
				case 'webkitallowfullscreen':
					// Omit these since amp-iframe will add them if needed if the `allowfullscreen` attribute is present.
					break;

				case 'loading':
					/*
					 * The `amp-iframe` component already does lazy-loading by default; trigger a validation error only
					 * if the value is not `lazy`.
					 */
					if ( 'lazy' !== strtolower( $value ) ) {
						$out[ $name ] = $value;
					}
					break;

				case 'security':
					/*
					 * Omit the `security` attribute as it now been superseded by the `sandbox` attribute. It is
					 * (apparently) only supported by IE <https://stackoverflow.com/a/20071528>.
					 */
					break;

				case 'marginwidth':
				case 'marginheight':
					// These attributes have been obsolete since HTML5. If they have the value `0` they can be omitted.
					if ( '0' !== $value ) {
						$out[ $name ] = $value;
					}
					break;

				case 'data-amp-resizable':
					$out['resizable'] = '';
					break;

				case 'data-amp-overflow-text':
					// No need to copy.
					break;

				case 'type':
					/*
					 * Omit the `type` attribute if its value is `text/html`. Popular embed providers such as Amazon
					 * Kindle use this non-standard attribute, which is apparently a vestige from usage on <object>.
					 */
					if ( 'text/html' !== strtolower( $value ) ) {
						$out[ $name ] = $value;
					}
					break;

				default:
					$out[ $name ] = $value;
					break;
			}
		}

		if ( ! isset( $out['sandbox'] ) ) {
			$out['sandbox'] = self::SANDBOX_DEFAULTS;
		}

		// Remove allow-same-origin from sandbox if required.
		if ( $remove_allow_same_origin ) {
			$out['sandbox'] = trim( preg_replace( '/(^|\s)allow-same-origin(\s|$)/', ' ', $out['sandbox'] ) );
		}

		return $out;
	}

	/**
	 * Obtain the origin part of a given URL (scheme, host, port).
	 *
	 * @param string $url URL.
	 * @return string|null Origin URL or null if parse failed.
	 */
	private function get_origin_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( ! isset( $parsed_url['host'] ) ) {
			return null;
		}
		if ( ! isset( $parsed_url['scheme'] ) ) {
			$parsed_url['scheme'] = wp_parse_url( $this->args['current_origin'], PHP_URL_SCHEME );
		}
		$origin  = $parsed_url['scheme'] . '://';
		$origin .= $parsed_url['host'];
		if ( isset( $parsed_url['port'] ) ) {
			$origin .= ':' . $parsed_url['port'];
		}
		return $origin;
	}

	/**
	 * Builds a DOMElement to use as a placeholder for an <iframe>.
	 *
	 * Important: The element returned must not be block-level (e.g. div) as the PHP DOM parser
	 * will move it out from inside any containing paragraph. So this is why a span is used.
	 *
	 * @since 0.2
	 *
	 * @return DOMElement|false
	 */
	private function build_placeholder() {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			'span',
			[
				'placeholder' => '',
				'class'       => 'amp-wp-iframe-placeholder',
			]
		);
	}

	/**
	 * Sanitizes a boolean character (or string) into a '0' or '1' character.
	 *
	 * @param string $value A boolean character to sanitize. If a string containing more than a single
	 *                      character is provided, only the first character is taken into account.
	 *
	 * @return string Returns either '0' or '1'.
	 */
	private function sanitize_boolean_digit( $value ) {

		// Default to false if the value was forgotten.
		if ( empty( $value ) ) {
			return '0';
		}

		// Default to false if the value has an unexpected type.
		if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
			return '0';
		}

		// See: https://github.com/ampproject/amp-wp/issues/2335#issuecomment-493209861.
		switch ( substr( (string) $value, 0, 1 ) ) {
			case '1':
			case 'y':
			case 'Y':
				return '1';
		}

		return '0';
	}
}
