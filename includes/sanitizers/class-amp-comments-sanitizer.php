<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Amp;
use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;

/**
 * Class AMP_Comments_Sanitizer
 *
 * Strips and corrects attributes in forms.
 *
 * @internal
 */
class AMP_Comments_Sanitizer extends AMP_Base_Sanitizer {

	/** @var AMP_Style_Sanitizer */
	private $style_sanitizer;

	/**
	 * Default args.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'comments_live_list'       => false,
		'ampify_comment_threading' => 'always', // Can be 'always', 'never', 'conditionally' (if the comment form was converted to action-xhr).
	];

	/**
	 * Init.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {
		parent::init( $sanitizers );

		if (
			array_key_exists( AMP_Style_Sanitizer::class, $sanitizers )
			&&
			$sanitizers[ AMP_Style_Sanitizer::class ] instanceof AMP_Style_Sanitizer
		) {
			$this->style_sanitizer = $sanitizers[ AMP_Style_Sanitizer::class ];
		}
	}

	/**
	 * Pre-process the comment form and comment list for AMP.
	 *
	 * @since 0.7
	 */
	public function sanitize() {

		// Find the comment form (which may not have the commentform ID).
		$comment_form = null;
		foreach ( $this->dom->getElementsByTagName( Tag::FORM ) as $form ) {
			$action = $form->getAttribute( Attribute::ACTION_XHR );
			if ( ! $action ) {
				$action = $form->getAttribute( Attribute::ACTION );
			}
			$action_path = wp_parse_url( $action, PHP_URL_PATH );
			if ( $action_path && 'wp-comments-post.php' === basename( $action_path ) ) {
				$comment_form = $form;
				break;
			}
		}

		// Handle the comment reply script.
		$comment_reply_script            = $this->get_comment_reply_script();
		$should_ampify_comment_threading = 'always' === $this->args['ampify_comment_threading'];
		if ( $comment_reply_script instanceof Element ) {
			if ( 'never' === $this->args['ampify_comment_threading'] ) {
				$this->prepare_native_comment_reply( $comment_reply_script );
				$should_ampify_comment_threading = false;
			} elseif (
				'always' === $this->args['ampify_comment_threading']
				||
				(
					'conditionally' === $this->args['ampify_comment_threading']
					&&
					(
						// If there isn't even a comment form on the page, then the comment-reply script shouldn't be here at all.
						! $comment_form instanceof Element
						||
						// If the form has an action-xhr attribute, then it's going to be an AMP page and we need ampified comment threading.
						$comment_form->hasAttribute( Attribute::ACTION_XHR )
					)
				)
			) {
				// Remove the script and then proceed with the amp-bind implementation below.
				$comment_reply_script->parentNode->removeChild( $comment_reply_script );
				$should_ampify_comment_threading = true;
			} else {
				// This is the conditionally-no case.
				$this->prepare_native_comment_reply( $comment_reply_script );

				// Do not proceed with the AMP-bind implementation for threaded comments since the comment-reply script was included.
				$should_ampify_comment_threading = false;
			}
		}

		// Now based on the comment reply script handling above, ampify the comment form.
		if ( get_option( 'thread_comments' ) && $comment_form && $should_ampify_comment_threading ) {
			$this->ampify_threaded_comments( $comment_form );
		}

		if ( $this->args['comments_live_list'] ) {
			$comments = $this->dom->xpath->query( '//amp-live-list/*[ @items ]/*[ starts-with( @id, "comment-" ) ]' );

			foreach ( $comments as $comment ) {
				$this->add_amp_live_list_comment_attributes( $comment );
			}
		}
	}

	/**
	 * Get comment reply script.
	 *
	 * @return Element|null
	 */
	protected function get_comment_reply_script() {
		$element = $this->dom->getElementById( 'comment-reply-js' );
		if ( $element instanceof Element && Tag::SCRIPT === $element->tagName ) {
			return $element;
		} else {
			return null;
		}
	}

	/**
	 * Ampify threaded comments by utilizing amp-bind to implement comment reply functionality.
	 *
	 * @param Element $comment_form Comment form.
	 */
	protected function ampify_threaded_comments( Element $comment_form ) {

		// Create reply state.
		$amp_state = $this->dom->createElement( Extension::STATE );
		$comment_form->insertBefore( $amp_state, $comment_form->firstChild );
		$state_id = 'ampCommentThreading';
		$amp_state->setAttribute( Attribute::ID, $state_id );
		$state = [
			'replyTo'       => '',
			'commentParent' => '0', // @todo What if page accessed with replytocom? Then this should be $comment_parent_id below.
		];

		$comment_parent_id    = 0;
		$comment_parent_input = $this->dom->getElementById( 'comment_parent' );
		if ( $comment_parent_input instanceof Element ) {
			$comment_parent_id = (int) $comment_parent_input->getAttribute( Attribute::VALUE );

			$comment_parent_input->setAttribute(
				Amp::BIND_DATA_ATTR_PREFIX . 'value',
				sprintf( '%s.commentParent', $state_id )
			);
		}

		// Add amp-state to the document.
		$script = $this->dom->createElement( Tag::SCRIPT );
		$script->setAttribute( Attribute::TYPE, 'application/json' );
		$script->appendChild( $this->dom->createTextNode( wp_json_encode( $state, JSON_UNESCAPED_UNICODE ) ) );
		$amp_state->appendChild( $script );

		// @todo This should also remove the novalidate attribute from the form.
		// Reset state when submitting form.
		$comment_form->addAmpAction(
			'submit-success',
			sprintf(
				'%s.clear,AMP.setState({%s: %s})',
				$this->dom->getElementId( $comment_form ),
				$state_id,
				wp_json_encode( $state )
			)
		);

		// Prepare the comment form for replies. The logic here corresponds to what is found in comment-reply.js.
		$reply_heading_element   = $this->dom->getElementById( 'reply-title' );
		$reply_heading_text_node = null; // The text node for the heading.
		if ( $reply_heading_element && $reply_heading_element->firstChild instanceof DOMText ) {
			$reply_heading_text_node = $reply_heading_element->firstChild;
		}

		// Add the text binding to the heading text, which necessitates wrapping in a span.
		if ( $reply_heading_text_node ) {
			$reply_heading_text_span = $this->dom->createElement( Tag::SPAN );
			$reply_heading_element->replaceChild( $reply_heading_text_span, $reply_heading_text_node );
			$reply_heading_text_span->appendChild( $reply_heading_text_node );

			// Move whitespace after the node.
			$reply_heading_text_node->nodeValue = rtrim( $reply_heading_text_node->nodeValue );
			$reply_heading_element->insertBefore(
				$this->dom->createTextNode( ' ' ),
				$reply_heading_text_span->nextSibling
			);

			// Note: if the replytocom query parameter was set, then the existing value will already be a replyTo value.
			// Nevertheless, the link will have the replytocom arg removed, so clicking on the link will cause
			// navigation to a page that has the nameless heading text.
			$text_binding = sprintf(
				'%1$s.replyTo ? %1$s.replyTo : %2$s',
				$state_id,
				wp_json_encode( $reply_heading_text_node->nodeValue, JSON_UNESCAPED_UNICODE )
			);

			$reply_heading_text_span->setAttribute(
				Amp::BIND_DATA_ATTR_PREFIX . 'text',
				$text_binding
			);
		}

		// Update comment reply links to set the reply state.
		$comment_reply_links = $this->dom->xpath->query( '//a[ @data-commentid and @data-postid and @data-replyto and @data-respondelement and contains( @class, "comment-reply-link" ) ]' );
		foreach ( $comment_reply_links as $comment_reply_link ) {
			/** @var Element $comment_reply_link */

			$comment_reply_state = [
				$state_id => [
					'replyTo'       => $comment_reply_link->getAttribute( 'data-replyto' ),
					'commentParent' => $comment_reply_link->getAttribute( 'data-commentid' ),
				],
			];

			$comment_reply_link->setAttribute(
				Attribute::HREF,
				'#' . $comment_reply_link->getAttribute( 'data-respondelement' )
			);

			$comment_reply_link->addAmpAction(
				'tap',
				sprintf(
					'AMP.setState(%s),comment.focus',
					wp_json_encode( $comment_reply_state, JSON_UNESCAPED_UNICODE )
				)
			);
		}

		$cancel_comment_reply_link = $this->dom->getElementById( 'cancel-comment-reply-link' );
		if ( $cancel_comment_reply_link instanceof Element ) {

			// Use hidden attribute to hide/show cancel reply link when commentParent is zero.
			$cancel_comment_reply_link->removeAttribute( Attribute::STYLE );
			if ( ! $comment_parent_id ) {
				$cancel_comment_reply_link->setAttributeNode( $this->dom->createAttribute( Attribute::HIDDEN ) );
			}
			$cancel_comment_reply_link->setAttribute(
				Amp::BIND_DATA_ATTR_PREFIX . Attribute::HIDDEN,
				sprintf( '%s.commentParent == "0"', $state_id )
			);

			// Reset state when clicking cancel.
			$cancel_comment_reply_link->addAmpAction(
				'tap',
				sprintf( 'AMP.setState({%s: %s})', $state_id, wp_json_encode( $state, JSON_UNESCAPED_UNICODE ) )
			);
		}
	}

	/**
	 * Prepare for native comment-reply functionality.
	 *
	 * @param Element $comment_reply_script Comment reply script.
	 */
	protected function prepare_native_comment_reply( Element $comment_reply_script ) {
		// Mark the comment-reply script as being PX-verified, which was not done in the script sanitizer because
		// we had to wait until after the form sanitizer ran to find out if we could conditionally serve valid AMP.
		ValidationExemption::mark_node_as_px_verified( $comment_reply_script );
		$comment_reply_script->setAttributeNode( $this->dom->createAttribute( Attribute::DEFER ) );

		// Make sure that that inline styles are not transformed or else they will break comment-reply styling.
		if ( $this->style_sanitizer ) {
			$this->style_sanitizer->update_args( [ 'transform_important_qualifiers' => false ] );
		}
	}

	/**
	 * Add attributes to comment elements when comments are being presented in amp-live-list, when comments_live_list theme support flag is present.
	 *
	 * @since 1.1
	 *
	 * @param Element $comment_element Comment element.
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
