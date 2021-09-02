<?php
/**
 * Class AMP_Form_Sanitizer.
 *
 * @package AMP
 * @since 0.7
 */

use AmpProject\DevMode;
use AmpProject\Dom\Document\Filter\MustacheScriptTemplates;
use AmpProject\Dom\Element;

/**
 * Class AMP_Form_Sanitizer
 *
 * Strips and corrects attributes in forms.
 *
 * @since 0.7
 * @internal
 */
class AMP_Form_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Validation error code emitted when there is a POST form with action-xhr but only native POST forms are used.
	 *
	 * @var string
	 */
	const POST_FORM_HAS_ACTION_XHR_WHEN_NATIVE_USED = 'POST_FORM_HAS_ACTION_XHR_WHEN_NATIVE_USED';

	/**
	 * Placeholder for default args, to be set in child classes.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'native_post_forms_used' => false,
	];

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type bool $native_post_forms_used When true, the amp-form extension will be omitted and any form[method=post][action] elements will not be converted into [action-xhr].
	 * }
	 */
	protected $args;

	/**
	 * Tag.
	 *
	 * @var string HTML <form> tag to identify and process.
	 *
	 * @since 0.7
	 */
	public static $tag = 'form';

	/**
	 * Sanitize the <form> elements from the HTML contained in this instance's Dom\Document.
	 *
	 * @link https://www.ampproject.org/docs/reference/components/amp-form
	 * @since 0.7
	 */
	public function sanitize() {

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $nodes
		 */
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof Element || DevMode::hasExemptionForNode( $node ) ) {
				continue;
			}

			// In HTML, the default method is 'get'.
			$method = 'get';
			if ( $node->getAttribute( 'method' ) ) {
				$method = strtolower( $node->getAttribute( 'method' ) );
			} else {
				$node->setAttribute( 'method', $method );
			}

			$action_url = $this->get_action_url( $node->getAttribute( 'action' ) );

			$xhr_action = $node->getAttribute( 'action-xhr' );

			// Make HTTP URLs protocol-less, since HTTPS is required for forms.
			if ( 'http://' === strtolower( substr( $action_url, 0, 7 ) ) ) {
				$action_url = substr( $action_url, 5 );
			}

			/*
			 * According to the AMP spec:
			 * For GET submissions, provide at least one of action or action-xhr.
			 * This attribute is required for method=GET. For method=POST, the
			 * action attribute is invalid, use action-xhr instead.
			 */
			if ( 'get' === $method ) {
				if ( $action_url !== $node->getAttribute( 'action' ) ) {
					$node->setAttribute( 'action', $action_url );
				}
			} elseif ( 'post' === $method ) {
				// If native post forms are used, then mark any POST forms as being unvalidated for AMP. Note that it is
				// an all or nothing proposition with forms, where there cannot be some POST forms with [action] and
				// others with [action-xhr]. The former is incompatible with the amp-form extension but the latter
				// fundamentally depends on it. So it's one or the other.
				if ( $this->args['native_post_forms_used'] ) {
					if ( $xhr_action ) {
						// @todo Consider rewriting action-xhr to action? Or include a shim which implements the amp-form functionality?
						$this->remove_invalid_child(
							$node,
							[ 'code' => self::POST_FORM_HAS_ACTION_XHR_WHEN_NATIVE_USED ]
						);
						continue;
					}

					$node->setAttributeNode(
						$this->dom->createAttribute( AMP_Validation_Manager::AMP_UNVALIDATED_TAG_ATTRIBUTE )
					);
					$this->dom->documentElement->setAttributeNode(
						$this->dom->createAttribute( AMP_Validation_Manager::AMP_NON_VALID_DOC_ATTRIBUTE )
					);
					continue;
				}

				$node->removeAttribute( 'action' );
				if ( ! $xhr_action ) {
					// Record that action was converted to action-xhr.
					$action_url = add_query_arg( AMP_HTTP::ACTION_XHR_CONVERTED_QUERY_VAR, 1, $action_url );
					$node->setAttribute( 'action-xhr', $action_url );
					// Append success/error handlers if not found.
					$this->ensure_response_message_elements( $node );
				} elseif ( 'http://' === substr( $xhr_action, 0, 7 ) ) {
					$node->setAttribute( 'action-xhr', substr( $xhr_action, 5 ) );
				}
			}

			$this->normalize_target_attribute( $node );
		}
	}

	/**
	 * Normalize form target attribute.
	 *
	 * The target "indicates where to display the form response after submitting the form.
	 * The value must be _blank or _top". The _self and _parent values are treated
	 * as synonymous with _top, and anything else is treated like _blank.
	 *
	 * @param Element $form_element Form element.
	 */
	protected function normalize_target_attribute( Element $form_element ) {
		$target = $form_element->getAttribute( 'target' );
		if ( '_top' !== $target ) {
			if ( ! $target || in_array( $target, [ '_self', '_parent' ], true ) ) {
				$form_element->setAttribute( 'target', '_top' );
			} elseif ( '_blank' !== $target ) {
				$form_element->setAttribute( 'target', '_blank' );
			}
		}
	}

	/**
	 * Get the action URL for the form element.
	 *
	 * @param string $action_url Action URL.
	 * @return string Action URL.
	 */
	protected function get_action_url( $action_url ) {
		/*
		 * In HTML, the default action is just the current URL that the page is served from.
		 * The action "specifies a server endpoint to handle the form input. The value must be an
		 * https URL and must not be a link to a CDN".
		 */
		if ( ! $action_url ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$parsed_url = wp_parse_url( $action_url );

		if (
			// Ignore a malformed URL - it will be later sanitized.
			false === $parsed_url
			||
			// Ignore HTTPS URLs, because there is nothing left to do.
			( isset( $parsed_url['scheme'] ) && 'https' === $parsed_url['scheme'] )
			||
			// Ignore protocol-relative URLs, because there is also nothing left to do.
			( ! isset( $parsed_url['scheme'] ) && isset( $parsed_url['host'] ) )
		) {
			return $action_url;
		}

		// Make URL protocol relative.
		$parsed_url['scheme'] = '//';

		// Set an empty path if none is defined but there is a host.
		if ( ! isset( $parsed_url['path'] ) && isset( $parsed_url['host'] ) ) {
			$parsed_url['path'] = '';
		}

		if ( ! isset( $parsed_url['host'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$parsed_url['host'] = $_SERVER['HTTP_HOST'];
		}

		if ( ! isset( $parsed_url['path'] ) ) {
			// If there is action URL path, use the one from the request.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$parsed_url['path'] = trailingslashit( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		} elseif ( '' !== $parsed_url['path'] && '/' !== $parsed_url['path'][0] ) {
			// If the path is relative, append it to the current request path.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$parsed_url['path'] = trailingslashit( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . trailingslashit( $parsed_url['path'] );
		}

		// Rebuild the URL.
		$action_url = $parsed_url['scheme'];
		if ( isset( $parsed_url['user'] ) ) {
			$action_url .= $parsed_url['user'];
			if ( isset( $parsed_url['pass'] ) ) {
				$action_url .= ':' . $parsed_url['pass'];
			}
			$action_url .= '@';
		}
		$action_url .= $parsed_url['host'];
		if ( isset( $parsed_url['port'] ) ) {
			$action_url .= ':' . $parsed_url['port'];
		}
		$action_url .= $parsed_url['path'];
		if ( isset( $parsed_url['query'] ) ) {
			$action_url .= '?' . $parsed_url['query'];
		}
		if ( isset( $parsed_url['fragment'] ) ) {
			$action_url .= '#' . $parsed_url['fragment'];
		}

		return esc_url_raw( $action_url );
	}

	/**
	 * Ensure that the form has a submit-success and submit-error element templates.
	 *
	 * @link https://www.ampproject.org/docs/reference/components/amp-form#success/error-response-rendering
	 * @since 1.2
	 *
	 * @param DOMElement $form The form node to check.
	 */
	public function ensure_response_message_elements( $form ) {
		$elements = [
			'submit-error'   => null,
			'submit-success' => null,
			'submitting'     => null,
		];

		$templates = $this->dom->xpath->query( MustacheScriptTemplates::XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY, $form );
		foreach ( $templates as $template ) {
			$parent = $template->parentNode;
			if ( $parent instanceof DOMElement ) {
				foreach ( array_keys( $elements ) as $attribute ) {
					if ( $parent->hasAttribute( $attribute ) ) {
						$elements[ $attribute ] = $parent;
					}
				}
			}
		}

		foreach ( $elements as $attribute => $element ) {
			if ( $element ) {
				continue;
			}
			$div      = $this->dom->createElement( 'div' );
			$template = $this->dom->createElement( 'template' );
			$div->setAttribute( 'class', 'amp-wp-default-form-message' );
			if ( 'submitting' === $attribute ) {
				$p = $this->dom->createElement( 'p' );
				$p->appendChild( $this->dom->createTextNode( __( 'Submitting…', 'amp' ) ) );
				$template->appendChild( $p );
			} else {
				$p = $this->dom->createElement( 'p' );
				$p->setAttribute( 'class', '{{#redirecting}}amp-wp-form-redirecting{{/redirecting}}' );
				// phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation -- Sanitization applied via amp-mustache.
				$p->appendChild( $this->dom->createTextNode( '{{#message}}{{{message}}}{{/message}}' ) );

				// Show generic message for HTTP success/failure.
				$p->appendChild( $this->dom->createTextNode( '{{^message}}' ) );
				if ( 'submit-error' === $attribute ) {
					$p->appendChild( $this->dom->createTextNode( __( 'Your submission failed.', 'amp' ) ) );
					/* translators: %1$s: HTTP status text, %2$s: HTTP status code */
					$reason = sprintf( __( 'The server responded with %1$s (code %2$s).', 'amp' ), '{{status_text}}', '{{status_code}}' );
				} else {
					$p->appendChild( $this->dom->createTextNode( __( 'It appears your submission was successful.', 'amp' ) ) );
					$reason = __( 'Even though the server responded OK, it is possible the submission was not processed.', 'amp' );
				}
				$reason .= ' ' . __( 'Please contact the developer of this form processor to improve this message.', 'amp' );

				$p->appendChild( $this->dom->createTextNode( ' ' ) );
				$small = $this->dom->createElement( 'small' );
				$small->appendChild( $this->dom->createTextNode( $reason ) );
				$small->appendChild( $this->dom->createTextNode( ' ' ) );
				$link = $this->dom->createElement( 'a' );
				$link->setAttribute( 'href', 'https://amp-wp.org/?p=5463' );
				$link->setAttribute( 'target', '_blank' );
				$link->setAttribute( 'rel', 'nofollow noreferrer noopener' );
				$link->appendChild( $this->dom->createTextNode( __( 'Learn More', 'amp' ) ) );
				$small->appendChild( $link );
				$p->appendChild( $small );

				$p->appendChild( $this->dom->createTextNode( '{{/message}}' ) );
				$template->appendChild( $p );
			}
			$div->setAttribute( $attribute, '' );
			$template->setAttribute( 'type', 'amp-mustache' );
			$div->appendChild( $template );
			$form->appendChild( $div );
		}
	}
}
