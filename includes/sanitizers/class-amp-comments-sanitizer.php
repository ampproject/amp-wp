<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Comments_Sanitizer
 *
 * Strips and corrects attributes in forms.
 */
class AMP_Comments_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize the comments list from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.7
	 */
	public function sanitize() {

		foreach ( $this->dom->getElementsByTagName( 'form' ) as $comment_form ) {
			/**
			 * Comment form.
			 *
			 * @var DOMElement $comment_form
			 */
			$action = $comment_form->getAttribute( 'action-xhr' );
			if ( ! $action ) {
				$action = $comment_form->getAttribute( 'action' );
			}
			$action_path = wp_parse_url( $action, PHP_URL_PATH );
			if ( ! preg_match( '#/wp-comments-post\.php$#', $action_path ) ) {
				continue;
			}

			$amp_state = $this->dom->createElement( 'amp-state' );
			$state_id  = sanitize_key( $comment_form->getAttribute( 'id' ) ) . 'Values';
			$amp_state->setAttribute( 'id', $state_id );

			$script = $this->dom->createElement( 'script' );
			$script->setAttribute( 'type', 'application/json' );
			$amp_state->appendChild( $script );

			$form_state = array();

			foreach ( $comment_form->getElementsByTagName( 'input' ) as $input ) {
				/**
				 * Input.
				 *
				 * @var DOMElement $input
				 */
				$name = $input->getAttribute( 'name' );
				if ( ! $name ) {
					continue;
				}

				// @todoRadio and checkbox inputs are not supported yet.
				$type = strtolower( $input->getAttribute( 'type' ) );
				if ( 'radio' === $type || 'checkbox' === $type ) {
					continue;
				}

				$form_state[ $name ] = $input->getAttribute( 'value' );
				if ( ! isset( $form_state[ $name ] ) ) {
					$form_state[ $name ] = '';
				}

				$input->setAttribute( AMP_DOM_Utils::get_amp_bind_placeholder_prefix() . 'value', "$state_id.$name" );
			}
			foreach ( $comment_form->getElementsByTagName( 'textarea' ) as $textarea ) {
				/**
				 * Textarea.
				 *
				 * @var DOMElement $textarea
				 */
				$name = $textarea->getAttribute( 'name' );
				if ( ! $name ) {
					continue;
				}
				$form_state[ $name ] = $textarea->textContent;

				$textarea->setAttribute( AMP_DOM_Utils::get_amp_bind_placeholder_prefix() . 'text', "$state_id.$name" );

				// Update the state in response to changing the input.
				$textarea->setAttribute( 'on', sprintf(
					'change:AMP.setState({ %s: { %s: event.value } })',
					$state_id,
					wp_json_encode( $name )
				) );
			}

			$script->appendChild( $this->dom->createTextNode( wp_json_encode( $form_state ) ) );
			$comment_form->insertBefore( $amp_state, $comment_form->firstChild );

			// Reset the form when successful.
			$reset_state = $form_state;
			unset( $reset_state['author'], $reset_state['email'], $reset_state['url'] ); // These remain the same after a submission.
			$comment_form->setAttribute( 'on', sprintf(
				'submit-success:AMP.setState( { %s: %s } )',
				wp_json_encode( $state_id ),
				wp_json_encode( $reset_state )
			) );
		}
	}
}
