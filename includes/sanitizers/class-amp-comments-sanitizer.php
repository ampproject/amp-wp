<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Tag;

/**
 * Class AMP_Comments_Sanitizer
 *
 * Strips and corrects attributes in forms.
 *
 * @internal
 */
class AMP_Comments_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default args.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'comment_live_list'        => false, // @todo See <https://github.com/ampproject/amp-wp/issues/4624>.
		'comment_reply_js_printed' => false,
	];

	/**
	 * Pre-process the comment form and comment list for AMP.
	 *
	 * @todo Fix https://github.com/ampproject/amp-wp/issues/6231
	 *
	 * @since 0.7
	 */
	public function sanitize() {
		// @todo Check if the comment-reply script was printed.
		// @todo Add defer to comment-reply if the script _is_ on the page (and there were no dependencies).

		foreach ( $this->dom->getElementsByTagName( Tag::FORM ) as $comment_form ) {
			// Skip processing comment forms which have opted-out of conversion to amp-form.
			// Note that AMP_Form_Sanitizer runs before AMP_Comments_Sanitizer according to amp_get_content_sanitizers().
//			if ( $comment_form->hasAttribute( 'action' ) ) {
//				continue;
//			}

			$action = $comment_form->getAttribute( Attribute::ACTION_XHR );
//			if ( ! $action ) {
//				$action = $comment_form->getAttribute( Attribute::ACTION );
//			}
			$action_path = wp_parse_url( $action, PHP_URL_PATH );
			if ( $action_path && 'wp-comments-post.php' === basename( $action_path ) ) {
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
	 * Get the ID for the amp-state.
	 *
	 * @param int $post_id Post ID.
	 * @return string ID for amp-state.
	 */
	protected function get_comment_form_state_id( $post_id ) {
		return sprintf( 'commentform_post_%d', $post_id );
	}

	/**
	 * Comment form.
	 *
	 * @since 0.7
	 *
	 * @param DOMElement $comment_form Comment form.
	 */
	protected function process_comment_form( $comment_form ) {
		$form_fields = [];
		foreach ( $comment_form->getElementsByTagName( Tag::INPUT ) as $element ) {
			/** @var DOMElement $element */
			$name = $element->getAttribute( Attribute::NAME );
			if ( $name ) {
				$form_fields[ $name ][] = $element;
			}
		}

		foreach ( $comment_form->getElementsByTagName( Tag::TEXTAREA ) as $element ) {
			/** @var DOMElement $element */
			$name = $element->getAttribute( Attribute::NAME );
			if ( $name ) {
				$form_fields[ $name ][] = $element;
			}
		}

		/**
		 * Named input elements.
		 *
		 * @var DOMElement[][] $form_fields
		 */
		if ( empty( $form_fields['comment_post_ID'] ) ) {
			return;
		}
		$post_id  = (int) $form_fields['comment_post_ID'][0]->getAttribute( Attribute::VALUE );
		$state_id = $this->get_comment_form_state_id( $post_id );

		$form_state = [
			'values'     => [],
			'submitting' => false,
			'replyTo'    => '', // @todo This should rather be just replyToText.
		];

		$comment_parent_id = null;
		if ( ! empty( $form_fields['comment_parent'] ) ) {
			$comment_parent_id = (int) $form_fields['comment_parent'][0]->getAttribute( Attribute::VALUE );

			// @todo Obtain replyTo?
			if ( $comment_id ) {
//				$reply_comment = get_comment( $comment_id );
//				if ( $reply_comment ) {
//					$form_state['replyToName'] = $reply_comment->comment_author;
//				}
			}
		}

		$amp_bind_attr_format = Amp::BIND_DATA_ATTR_PREFIX . '%s';
		foreach ( $form_fields as $name => $form_field ) {
			foreach ( $form_field as $element ) {

				// @todo Radio and checkbox inputs are not supported yet.
				if ( in_array( strtolower( $element->getAttribute( Attribute::TYPE ) ), [ 'checkbox', 'radio' ], true ) ) {
					continue;
				}

				$element->setAttribute( sprintf( $amp_bind_attr_format, Attribute::DISABLED ), "$state_id.submitting" );

				if ( Tag::TEXTAREA === strtolower( $element->nodeName ) ) {
					$form_state['values'][ $name ] = $element->textContent;
					$element->setAttribute( sprintf( $amp_bind_attr_format, 'text' ), "$state_id.values.$name" );
				} else {
					$form_state['values'][ $name ] = $element->hasAttribute( Attribute::VALUE ) ? $element->getAttribute( Attribute::VALUE ) : '';
					$element->setAttribute( sprintf( $amp_bind_attr_format, Attribute::VALUE ), "$state_id.values.$name" );
				}

				// Update the state in response to changing the input.
				$element->setAttribute(
					Attribute::ON,
					sprintf(
						'change:AMP.setState( { %s: { values: { %s: event.value } } } )',
						$state_id,
						wp_json_encode( $name )
					)
				);
			}
		}

		// Add amp-state to the document.
		$amp_state = $this->dom->createElement( Extension::STATE );
		$amp_state->setAttribute( Attribute::ID, $state_id );
		$script = $this->dom->createElement( Tag::SCRIPT );
		$script->setAttribute( Attribute::TYPE, 'application/json' );
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
		$comment_form->setAttribute( Attribute::ON, implode( ';', $on ) );

		// @todo DO the filter_comment_form_defaults.
		$reply_heading_element   = $this->dom->getElementById( 'reply-title' );
		$reply_heading_text_node = null;
		$reply_link_to_parent    = null;
		if ( $reply_heading_element && $reply_heading_element->firstChild instanceof DOMText ) {
			$reply_heading_text_node = $reply_heading_element->firstChild;
		}
		if ( $reply_heading_text_node && $reply_heading_text_node->nextSibling instanceof DOMElement ) {
			$reply_link_to_parent = $reply_heading_text_node->nextSibling;
		}
		if ( $reply_heading_text_node && $reply_link_to_parent ) {
			$reply_heading_text_span = $this->dom->createElement( Tag::SPAN );
			$reply_heading_element->replaceChild( $reply_heading_text_span, $reply_heading_text_node );
			$reply_heading_text_span->appendChild( $reply_heading_text_node );

			// Move whitespace after the node.
			$reply_heading_text_node->nodeValue = rtrim( $reply_heading_text_node->nodeValue );
			$reply_heading_element->insertBefore(
				$this->dom->createTextNode( ' ' ),
				$reply_heading_text_span->nextSibling
			);

			// @todo Consider replytocom. $comment_parent_id
			$text_binding = sprintf(
				'%1$s.replyTo ? %1$s.replyTo : %2$s',
				$state_id,
//				str_replace(
//					'%s',
//					sprintf( '" + %s.replyToName + "', $state_id ),
//					wp_json_encode( $default_args['title_reply_to'], JSON_UNESCAPED_UNICODE )
//				),
				wp_json_encode( $reply_heading_text_node->nodeValue, JSON_UNESCAPED_UNICODE )
			);

			$reply_heading_text_span->setAttribute(
				Amp::BIND_DATA_ATTR_PREFIX . 'text',
				$text_binding
			);
		}

		// Populate amp-state.
		$amp_state->appendChild( $script );
		$script->appendChild( $this->dom->createTextNode( wp_json_encode( $form_state, JSON_UNESCAPED_UNICODE ) ) );

		$comment_reply_links = $this->dom->xpath->query( '//a[ @data-commentid and @data-postid and @data-replyto and @data-respondelement and contains( @class, "comment-reply-link" ) ]' );
		foreach ( $comment_reply_links as $comment_reply_link ) {
			/** @var DOMElement $comment_reply_link */

			$comment_reply_state = [
				$state_id => [
					'replyTo' => $comment_reply_link->getAttribute( 'data-replyto' ),
					'values'  => [
						'comment_parent' => $comment_reply_link->getAttribute( 'data-commentid' ),
					],
				],
			];

			$comment_reply_link->setAttribute(
				Attribute::HREF,
				'#' . $comment_reply_link->getAttribute( 'data-respondelement' )
			);

			$comment_reply_link->setAttribute(
				Attribute::ON,
				sprintf(
					'tap:AMP.setState(%s)',
					wp_json_encode( $comment_reply_state, JSON_UNESCAPED_UNICODE )
				)
			);
		}

		$cancel_comment_reply_link = $this->dom->getElementById( 'cancel-comment-reply-link' );
		if ( $cancel_comment_reply_link instanceof DOMElement ) {
			$cancel_comment_reply_link->removeAttribute( Attribute::STYLE );

			if ( ! $comment_parent_id ) {
				$cancel_comment_reply_link->setAttributeNode( $this->dom->createAttribute( Attribute::HIDDEN ) );
			}

			$tap_state = [
				$state_id => [
					'replyTo' => '',
					'values'  => [
						'comment_parent' => '0',
					],
				],
			];
			$cancel_comment_reply_link->setAttribute(
				Attribute::ON,
				sprintf( 'tap:AMP.setState( %s )', wp_json_encode( $tap_state, JSON_UNESCAPED_UNICODE ) )
			);

			$cancel_comment_reply_link->setAttribute(
				Amp::BIND_DATA_ATTR_PREFIX . Attribute::HIDDEN,
				sprintf( '%s.values.comment_parent == "0"', $state_id )
			);
		}


//		$reply_heading_text_node =
//		if ( $reply_heading_element ) {
//
//
//			var replyHeadingElement  = getElementById( config.commentReplyTitleId );
//			var replyHeadingTextNode = replyHeadingElement && replyHeadingElement.firstChild;
//			var replyLinkToParent    = replyHeadingTextNode && replyHeadingTextNode.nextSibling;
//
//		}
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
