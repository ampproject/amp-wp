<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Extension;
use AmpProject\Layout;
use AmpProject\Tag;

/**
 * Class AMP_Comments_Sanitizer
 *
 * Strips and corrects attributes in forms.
 */
class AMP_Comments_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * XPath query to retrieve the entire comments section that needs to be
	 * wrapped within an <amp-script>.
	 *
	 * This section needs to include at the very least the comment forms as well
	 * as all reply buttons.
	 *
	 * @since 1.6
	 *
	 * @var string
	 */
	const COMMENTS_SECTION_XPATH = 'comments_section_xpath';

	/**
	 * Default args.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'comment_live_list' => false,
		self::COMMENTS_SECTION_XPATH => './/*[ @id = "comments" ]',
	];

	/**
	 * Pre-process the comment form and comment list for AMP.
	 *
	 * @since 0.7
	 */
	public function sanitize() {
		$this->wrap_comments_section_in_amp_script();
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
			if ( preg_match( '#/wp-comments-post\.php$#', $action_path ) ) {
				$this->process_comment_form( $comment_form );
			}
		}

		if ( ! empty( $this->args['comments_live_list'] ) ) {
			$comments = $this->dom->xpath->query( '//amp-live-list/*[ @items ]/*[ starts-with( @id, "comment-" ) ]' );

			foreach ( $comments as $comment ) {
				$this->add_amp_live_list_comment_attributes( $comment );
			}
		}
	}

	/**
	 * Wrap the comments section wrapper element in an <amp-script> linking to
	 * the comment-reply.js source.
	 *
	 * The difficulty here lies in knowing at what parent level to wrap so that
	 * we catch both the main comment form as well as all reply buttons that we
	 * need to interact with.
	 */
	protected function wrap_comments_section_in_amp_script()
	{
		$comment_reply_js_src = amp_get_asset_url( 'js/comment-reply.js' );

		$comment_reply_js = sprintf(
			"console.log( 'Start of amp-script logic' );\n%s\ninit( document.body );\nconsole.log( 'End of amp-script logic' );\n/* Adapted version of %s*/\n",
			file_get_contents( $comment_reply_js_src ),
			$comment_reply_js_src
		);

		// Strip comments to get below 10k bytes.
		$comment_reply_js = preg_replace( '!/\*.*?\*/!s', '', $comment_reply_js );

		$comment_reply_script = $this->dom->createElement( Tag::SCRIPT );
		$comment_reply_script->setAttribute( Attribute::TYPE, Attribute::TYPE_TEXT_PLAIN );
		$comment_reply_script->setAttribute( Attribute::TARGET, Extension::SCRIPT );
		$comment_reply_script->appendChild( $this->dom->createTextNode( $comment_reply_js ) );

		$comment_reply_script_id = AMP_DOM_Utils::get_element_id( $comment_reply_script, 'amp-hello-world-script' );

		$comment_reply_script_hash = $this->dom->createElement( Tag::META );
		$comment_reply_script_hash->setAttribute( Attribute::NAME, 'amp-script-src' );
		$comment_reply_script_hash->setAttribute( Attribute::CONTENT, amp_generate_script_hash( $comment_reply_js ) );

		$amp_script = $this->dom->createElement( Extension::SCRIPT );
		$amp_script->setAttribute( Attribute::LAYOUT, Layout::CONTAINER );
		$amp_script->setAttribute( Attribute::SCRIPT, $comment_reply_script_id );
		$amp_script->setAttribute( Attribute::SANDBOX, 'allow-forms' );

		// @TODO: Should we iterate over all matches instead of just picking the first?
		$wrap_target = $this->dom->xpath
			->query( $this->args[ self::COMMENTS_SECTION_XPATH ] )
			->item( 0 );

		if ( ! $wrap_target instanceof DOMElement ) {
			return;
		}

		$this->dom->head->appendChild( $comment_reply_script_hash );

		$wrap_target = $wrap_target->parentNode->replaceChild( $amp_script, $wrap_target );
		$amp_script->appendChild( $wrap_target );

		$this->dom->body->appendChild( $comment_reply_script );
	}

	/**
	 * Comment form.
	 *
	 * @since 0.7
	 *
	 * @param DOMElement $comment_form Comment form.
	 */
	protected function process_comment_form( $comment_form ) {
		/**
		 * Element.
		 *
		 * @var DOMElement $element
		 */

		/**
		 * Named input elements.
		 *
		 * @var DOMElement[][] $form_fields
		 */
		$form_fields = [];
		foreach ( $comment_form->getElementsByTagName( 'input' ) as $element ) {
			$name = $element->getAttribute( 'name' );
			if ( $name ) {
				$form_fields[ $name ][] = $element;
			}
		}
		foreach ( $comment_form->getElementsByTagName( 'textarea' ) as $element ) {
			$name = $element->getAttribute( 'name' );
			if ( $name ) {
				$form_fields[ $name ][] = $element;
			}
		}

		if ( empty( $form_fields['comment_post_ID'] ) ) {
			return;
		}
		$post_id  = (int) $form_fields['comment_post_ID'][0]->getAttribute( 'value' );
		$state_id = AMP_Theme_Support::get_comment_form_state_id( $post_id );

		$form_state = [
			'values'      => [],
			'submitting'  => false,
			'replyToName' => '',
		];

		if ( ! empty( $form_fields['comment_parent'] ) ) {
			$comment_id = (int) $form_fields['comment_parent'][0]->getAttribute( 'value' );
			if ( $comment_id ) {
				$reply_comment = get_comment( $comment_id );
				if ( $reply_comment ) {
					$form_state['replyToName'] = $reply_comment->comment_author;
				}
			}
		}

		$amp_bind_attr_format = Document::AMP_BIND_DATA_ATTR_PREFIX . '%s';
		foreach ( $form_fields as $name => $form_field ) {
			foreach ( $form_field as $element ) {

				// @todo Radio and checkbox inputs are not supported yet.
				if ( in_array( strtolower( $element->getAttribute( 'type' ) ), [ 'checkbox', 'radio' ], true ) ) {
					continue;
				}

				$element->setAttribute( sprintf( $amp_bind_attr_format, 'disabled' ), "$state_id.submitting" );

				if ( 'textarea' === strtolower( $element->nodeName ) ) {
					$form_state['values'][ $name ] = $element->textContent;
					$element->setAttribute( sprintf( $amp_bind_attr_format, 'text' ), "$state_id.values.$name" );
				} else {
					$form_state['values'][ $name ] = $element->hasAttribute( 'value' ) ? $element->getAttribute( 'value' ) : '';
					$element->setAttribute( sprintf( $amp_bind_attr_format, 'value' ), "$state_id.values.$name" );
				}

				// Update the state in response to changing the input.
				$element->setAttribute(
					'on',
					sprintf(
						'change:AMP.setState( { %s: { values: { %s: event.value } } } )',
						$state_id,
						wp_json_encode( $name )
					)
				);
			}
		}

		// Add amp-state to the document.
		$amp_state = $this->dom->createElement( 'amp-state' );
		$amp_state->setAttribute( 'id', $state_id );
		$script = $this->dom->createElement( 'script' );
		$script->setAttribute( 'type', 'application/json' );
		$amp_state->appendChild( $script );
		$script->appendChild( $this->dom->createTextNode( wp_json_encode( $form_state, JSON_UNESCAPED_UNICODE ) ) );
		$comment_form->insertBefore( $amp_state, $comment_form->firstChild );

		// Update state when submitting form.
		$form_reset_state = $form_state;
		unset(
			$form_reset_state['values']['author'],
			$form_reset_state['values']['email'],
			$form_reset_state['values']['url']
		);
		$on = [
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
				wp_json_encode( $form_reset_state, JSON_UNESCAPED_UNICODE )
			),
		];
		$comment_form->setAttribute( 'on', implode( ';', $on ) );
	}

	/**
	 * Add attributes to comment elements when comments are being presented in amp-live-list, when comments_live_list theme support flag is present.
	 *
	 * @since 1.1
	 *
	 * @param DOMElement $comment_element Comment element.
	 */
	protected function add_amp_live_list_comment_attributes( $comment_element ) {
		$comment_id = (int) str_replace( 'comment-', '', $comment_element->getAttribute( 'id' ) );
		if ( ! $comment_id ) {
			return;
		}
		$comment_object = get_comment( $comment_id );

		// Skip if the comment is not valid or the comment has a parent, since in that case it is not relevant for amp-live-list.
		if ( ! ( $comment_object instanceof WP_Comment ) || $comment_object->comment_parent ) {
			return;
		}

		$comment_element->setAttribute( 'data-sort-time', strtotime( $comment_object->comment_date ) );

		$update_time = strtotime( $comment_object->comment_date );

		// Ensure the top-level data-update-time reflects the max time of the comments in the thread.
		$children = $comment_object->get_children(
			[
				'format'       => 'flat',
				'hierarchical' => 'flat',
				'orderby'      => 'none',
			]
		);
		foreach ( $children as $child_comment ) {
			$update_time = max( strtotime( $child_comment->comment_date ), $update_time );
		}

		$comment_element->setAttribute( 'data-update-time', $update_time );
	}
}
