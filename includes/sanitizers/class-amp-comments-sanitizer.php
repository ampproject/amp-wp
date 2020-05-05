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

	const COMMENTS_REPLY_FORM_XPATH = 'comments_reply_form_xpath';

	const COMMENT_REPLY_LINK_XPATH = 'comment_reply_link_xpath';

	const CANCEL_COMMENT_REPLY_LINK_XPATH = 'cancel_comment_reply_link_xpath';

	const COMMENTS_SECTION_XPATH_DEFAULT_QUERY = './/*[ @id = "comments" ]';

	const COMMENTS_REPLY_FORM_XPATH_DEFAULT_QUERY = './/*[ @id = "respond" ]';

	const COMMENT_REPLY_LINK_XPATH_DEFAULT_QUERY = '//*[ contains( concat( " ", normalize-space( @class ), " " ), " comment-reply-link " ) ]';

	const CANCEL_COMMENT_REPLY_LINK_XPATH_DEFAULT_QUERY = './/*[ @id = "cancel-comment-reply-link" ]';

	/**
	 * Default args.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'comment_live_list'                   => false,
		self::COMMENTS_SECTION_XPATH          => self::COMMENTS_SECTION_XPATH_DEFAULT_QUERY,
		self::COMMENTS_REPLY_FORM_XPATH       => self::COMMENTS_REPLY_FORM_XPATH_DEFAULT_QUERY,
		self::COMMENT_REPLY_LINK_XPATH        => self::COMMENT_REPLY_LINK_XPATH_DEFAULT_QUERY,
		self::CANCEL_COMMENT_REPLY_LINK_XPATH => self::CANCEL_COMMENT_REPLY_LINK_XPATH_DEFAULT_QUERY,
	];

	/**
	 * Pre-process the comment form and comment list for AMP.
	 *
	 * @since 0.7
	 */
	public function sanitize() {
		$comments_section = $this->detect_comments_section();
		if ( $comments_section instanceof DOMElement ) {
			$comments_section = $this->wrap_comments_section_in_amp_script( $comments_section );
			$this->remove_hrefs_from_reply_links( $comments_section );
		}

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

	protected function detect_comments_section() {
		// @TODO: Should we iterate over all matches instead of just picking the first?
		$comments_section = $this->dom->xpath
			->query( $this->args[ self::COMMENTS_SECTION_XPATH ] )
			->item( 0 );

		$reply_form = $this->dom->xpath
			->query( $this->args[ self::COMMENTS_REPLY_FORM_XPATH ] )
			->item( 0 );

		if ( ! $comments_section instanceof DOMElement && ! $reply_form instanceof DOMElement ) {
			return false;
		}

		if ( ! $comments_section instanceof DOMElement ) {
			return $reply_form;
		}

		if ( ! $reply_form instanceof DOMElement ) {
			return $comments_section;
		}

		return $this->find_common_ancestor( $comments_section, $reply_form );
	}

	protected function find_common_ancestor( DOMElement $first, DOMElement $second ) {
		if ( $first->isSameNode( $second ) ) {
			return $first;
		}

		$first_parents  = $this->get_parent_nodes( $first );
		$second_parents = $this->get_parent_nodes( $second );

		$common_ancestor = false;

		$first_top_most_parent  = array_pop( $first_parents );
		$second_top_most_parent = array_pop( $second_parents );
		while ( $first_top_most_parent->isSameNode( $second_top_most_parent ) ) {
			$common_ancestor        = $first_top_most_parent;
			$first_top_most_parent  = array_pop( $first_parents );
			$second_top_most_parent = array_pop( $second_parents );
		}

		return $common_ancestor;
	}

	protected function get_parent_nodes( DOMElement $initial_node ) {
		$parent_nodes = [];
		$parent_node = $initial_node->parentNode;

		while ( $parent_node instanceof DOMElement ) {
			$parent_nodes[] = $parent_node;
			$parent_node = $parent_node->parentNode;
		}

		return $parent_nodes;
	}

	/**
	 * Wrap the comments section wrapper element in an <amp-script> linking to
	 * the comment-reply.js source.
	 *
	 * The difficulty here lies in knowing at what parent level to wrap so that
	 * we catch both the main comment form as well as all reply buttons that we
	 * need to interact with.
	 *
	 * @param DOMElement $wrap_target Target to wrap with the <amp-script>.
	 */
	protected function wrap_comments_section_in_amp_script( DOMElement $wrap_target )
	{
		$comment_reply_js_src = AMP__DIR__ . '/assets/js/comment-reply.js';

		$comment_reply_js = sprintf(
			"console.log( 'Start of amp-script logic' );\n%s\nconsole.log( 'End of amp-script logic' );\n",
			file_get_contents( $comment_reply_js_src ),
			$comment_reply_js_src
		);

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

		$this->dom->head->appendChild( $comment_reply_script_hash );

		$wrap_target = $wrap_target->parentNode->replaceChild( $amp_script, $wrap_target );
		$amp_script->appendChild( $wrap_target );

		$this->dom->body->appendChild( $comment_reply_script );

		return $wrap_target;
	}

	protected function remove_hrefs_from_reply_links( DOMElement $comments_section ) {
		$reply_links = $this->dom->xpath
			->query( $this->args[ self::COMMENT_REPLY_LINK_XPATH ], $comments_section );

		foreach ( $reply_links as $reply_link ) {
			/**
			 * Reply link to remove the href attribute from.
			 *
			 * @var DOMElement $reply_link
			 */
			$reply_link->removeAttribute( 'href' );
		}

		$cancel_comment_reply_link = $this->dom->xpath
			->query( $this->args[ self::CANCEL_COMMENT_REPLY_LINK_XPATH ], $comments_section )
			->item( 0 );

		if ( ! $cancel_comment_reply_link instanceof DOMElement ) {
			return;
		}

		$cancel_comment_reply_link->removeAttribute( 'href' );
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
