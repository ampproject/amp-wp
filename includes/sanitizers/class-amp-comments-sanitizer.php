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
	 * Pre-process the comment form and comment list for AMP.
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
			$state_id  = sanitize_key( $comment_form->getAttribute( 'id' ) ) . '_props';
			$amp_state->setAttribute( 'id', $state_id );

			$script = $this->dom->createElement( 'script' );
			$script->setAttribute( 'type', 'application/json' );
			$amp_state->appendChild( $script );

			$form_state = array(
				'values'     => array(),
				'submitting' => false,
			);

			$amp_bind_attr_format = AMP_DOM_Utils::get_amp_bind_placeholder_prefix() . '%s';
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

				// @todo Radio and checkbox inputs are not supported yet.
				$type = strtolower( $input->getAttribute( 'type' ) );
				if ( in_array( $type, array( 'checkbox', 'radio' ), true ) ) {
					continue;
				}

				$form_state['values'][ $name ] = $input->getAttribute( 'value' );
				if ( ! isset( $form_state['values'][ $name ] ) ) {
					$form_state['values'][ $name ] = '';
				}

				$input->setAttribute( sprintf( $amp_bind_attr_format, 'value' ), "$state_id.values.$name" );
				$input->setAttribute( sprintf( $amp_bind_attr_format, 'disabled' ), "$state_id.submitting" );
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
				$form_state['values'][ $name ] = $textarea->textContent;

				$textarea->setAttribute( sprintf( $amp_bind_attr_format, 'text' ), "$state_id.values.$name" );
				$textarea->setAttribute( sprintf( $amp_bind_attr_format, 'disabled' ), "$state_id.submitting" );

				// Update the state in response to changing the input.
				$textarea->setAttribute( 'on', sprintf(
					'change:AMP.setState( { %s: { values: { %s: event.value } } } )',
					$state_id,
					wp_json_encode( $name )
				) );
			}

			$script->appendChild( $this->dom->createTextNode( wp_json_encode( $form_state ) ) );
			$comment_form->insertBefore( $amp_state, $comment_form->firstChild );

			// Update state when submitting form.
			$form_reset_state = $form_state;
			unset(
				$form_reset_state['values']['author'],
				$form_reset_state['values']['email'],
				$form_reset_state['values']['url']
			);
			$on = array(
				// Disable the form when submitting.
				sprintf(
					'submit:AMP.setState( { %s: { submitting: true } } )',
					wp_json_encode( $state_id )
				),
				// Re-enable the form fields when the submission fails.
				sprintf(
					'submit-error:AMP.setState( { %s: { submitting: false } } )',
					wp_json_encode( $state_id )
				),
				// Reset the form to its initial state (with enabled form fields), except for the author, email, and url.
				sprintf(
					'submit-success:AMP.setState( { %s: %s } )',
					$state_id,
					wp_json_encode( $form_reset_state )
				),
			);
			$comment_form->setAttribute( 'on', implode( ';', $on ) );
		}
	}
}
