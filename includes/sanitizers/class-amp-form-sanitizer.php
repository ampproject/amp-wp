<?php
/**
 * Class AMP_Form_Sanitizer.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Form_Sanitizer
 *
 * Strips and corrects attributes in forms.
 *
 * @since 0.7
 */
class AMP_Form_Sanitizer extends AMP_Base_Sanitizer {

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
		 * @var DOMNodeList $node
		 */
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement || $this->has_dev_mode_exemption( $node ) ) {
				continue;
			}

			// In HTML, the default method is 'get'.
			$method = 'get';
			if ( $node->getAttribute( 'method' ) ) {
				$method = strtolower( $node->getAttribute( 'method' ) );
			} else {
				$node->setAttribute( 'method', $method );
			}

			$action_url = $this->get_action_url( $node );
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
				$node->removeAttribute( 'action' );
				if ( ! $xhr_action ) {
					// Record that action was converted to action-xhr.
					$action_url = add_query_arg( AMP_HTTP::ACTION_XHR_CONVERTED_QUERY_VAR, 1, $action_url );
					if ( ! amp_is_canonical() ) {
						$action_url = add_query_arg( amp_get_slug(), '', $action_url );
					}
					$node->setAttribute( 'action-xhr', $action_url );
					// Append success/error handlers if not found.
					$this->ensure_response_message_elements( $node );
				} elseif ( 'http://' === substr( $xhr_action, 0, 7 ) ) {
					$node->setAttribute( 'action-xhr', substr( $xhr_action, 5 ) );
				}
			}

			/*
			 * The target "indicates where to display the form response after submitting the form.
			 * The value must be _blank or _top". The _self and _parent values are treated
			 * as synonymous with _top, and anything else is treated like _blank.
			 */
			$target = $node->getAttribute( 'target' );
			if ( '_top' !== $target ) {
				if ( ! $target || in_array( $target, [ '_self', '_parent' ], true ) ) {
					$node->setAttribute( 'target', '_top' );
				} elseif ( '_blank' !== $target ) {
					$node->setAttribute( 'target', '_blank' );
				}
			}
		}
	}

	/**
	 * Get the action URL for the form element.
	 *
	 * @param DOMElement $form Form element.
	 *
	 * @return string Action URL.
	 */
	protected function get_action_url( DOMElement $form ) {
		/*
		 * In HTML, the default action is just the current URL that the page is served from.
		 * The action "specifies a server endpoint to handle the form input. The value must be an
		 * https URL and must not be a link to a CDN".
		 */
		if ( ! $form->getAttribute( 'action' ) ) {
			return esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$action_url = $form->getAttribute( 'action' );

		// Handle relative URLs.
		if ( ! preg_match( '#^(https?:)?//#', $action_url ) ) {
			$schemeless_host = '//' . $_SERVER['HTTP_HOST'];
			if ( '?' === $action_url[0] || '#' === $action_url[0] ) {
				// For actions consisting of only a query or URL fragment, include the schemeless-host and the REQUEST URI of the current page.
				$action_url = $schemeless_host . wp_unslash( $_SERVER['REQUEST_URI'] ) . $action_url;
			} elseif ( '.' === $action_url[0] ) {
				// For actions consisting of relative paths (e.g. '../'), prepend the schemeless-host and a trailing-slashed REQUEST URI.
				$action_url = $schemeless_host . trailingslashit( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . $action_url;
			} else {
				// Otherwise, when the action URL includes an absolute path, just append it to the schemeless-host.
				$action_url = $schemeless_host . $action_url;
			}
			return esc_url_raw( $action_url );
		}

		// Handle external URLs by use of a proxy.
		if ( wp_parse_url( home_url(), PHP_URL_HOST ) !== wp_parse_url( $action_url, PHP_URL_HOST ) ) {
			// @todo There needs to be some endpoint registered which serves as the proxy.
		}

		return $action_url;
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
		/**
		 * Parent node.
		 *
		 * @var DOMElement $parent
		 */
		$elements = [
			'submit-error'   => null,
			'submit-success' => null,
			'submitting'     => null,
		];

		$templates = $form->getElementsByTagName( 'template' );
		for ( $i = $templates->length - 1; $i >= 0; $i-- ) {
			$parent = $templates->item( $i )->parentNode;
			foreach ( array_keys( $elements ) as $attribute ) {
				if ( $parent->hasAttribute( $attribute ) ) {
					$elements[ $attribute ] = $parent;
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
