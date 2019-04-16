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
	 * Sanitize the <form> elements from the HTML contained in this instance's DOMDocument.
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
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			// In HTML, the default method is 'get'.
			$method = 'get';
			if ( $node->getAttribute( 'method' ) ) {
				$method = strtolower( $node->getAttribute( 'method' ) );
			} else {
				$node->setAttribute( 'method', $method );
			}

			/*
			 * In HTML, the default action is just the current URL that the page is served from.
			 * The action "specifies a server endpoint to handle the form input. The value must be an
			 * https URL and must not be a link to a CDN".
			 */
			if ( ! $node->getAttribute( 'action' ) ) {
				$action_url = esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . wp_unslash( $_SERVER['REQUEST_URI'] ) );
			} else {
				$action_url = $node->getAttribute( 'action' );
				// Check if action_url is a relative path and add the host to it.
				if ( ! preg_match( '#^(https?:)?//#', $action_url ) ) {
					$action_url = esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . $action_url );
				}
			}
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
					// record that action was converted tp action-xhr.
					$action_url = add_query_arg( '_wp_amp_action_xhr_converted', 1, $action_url );
					$node->setAttribute( 'action-xhr', $action_url );
					// Append error handler if not found.
					$this->ensure_submit_error_element( $node );
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
				if ( ! $target || in_array( $target, array( '_self', '_parent' ), true ) ) {
					$node->setAttribute( 'target', '_top' );
				} elseif ( '_blank' !== $target ) {
					$node->setAttribute( 'target', '_blank' );
				}
			}
		}
	}

	/**
	 * Checks if the form has an error handler else create one if not.
	 *
	 * @link https://www.ampproject.org/docs/reference/components/amp-form#success/error-response-rendering
	 * @since 0.7
	 *
	 * @param DOMElement $form The form node to check.
	 */
	public function ensure_submit_error_element( $form ) {
		$templates = $form->getElementsByTagName( 'template' );
		for ( $i = $templates->length - 1; $i >= 0; $i-- ) {
			if ( $templates->item( $i )->parentNode->hasAttribute( 'submit-error' ) ) {
				return; // Found error template, do nothing.
			}
		}

		$div      = $this->dom->createElement( 'div' );
		$template = $this->dom->createElement( 'template' );
		$mustache = $this->dom->createTextNode( '{{{error}}}' );
		$div->setAttribute( 'submit-error', '' );
		$template->setAttribute( 'type', 'amp-mustache' );
		$template->appendChild( $mustache );
		$div->appendChild( $template );
		$form->appendChild( $div );
	}
}
