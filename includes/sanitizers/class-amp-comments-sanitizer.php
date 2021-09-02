<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Element;
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
	 * XPath expression for script output to allow users with unfiltered_html to use that capability in top-level window.
	 *
	 * @see wp_comment_form_unfiltered_html_nonce()
	 * @var string
	 */
	const UNFILTERED_HTML_COMMENT_SCRIPT_XPATH = '
		//script[
			preceding-sibling::input[ @name = "_wp_unfiltered_html_comment_disabled" ]
			and
			contains( text(), "_wp_unfiltered_html_comment_disabled" )
		]
	';

	/**
	 * Default args.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'comment_live_list'        => false, // @todo See <https://github.com/ampproject/amp-wp/issues/4624>.
		'thread_comments'          => false, // By default maps to thread_comments option.
		'allow_commenting_scripts' => false, // @todo Change this to allow_native_comments which also adds px-verified to comment form instead of validation error.
	];

	/**
	 * Init.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {
		parent::init( $sanitizers );

		// When initializing with comment scripting allowed, let the script sanitizer know that the commenting scripts
		// have are PX-verified so that they won't get removed nor reported as validation errors.
		// @todo Or rather should this be accomplished via the amp_validation_error_default_sanitized filter?
		if (
			! empty( $this->args['allow_commenting_scripts'] )
			&&
			isset( $sanitizers[ AMP_Script_Sanitizer::class ] )
			&&
			$sanitizers[ AMP_Script_Sanitizer::class ] instanceof AMP_Script_Sanitizer
		) {
			$script_sanitizer = $sanitizers[ AMP_Script_Sanitizer::class ];

			// @todo Why go the trouble of this? Add attributes below instead.
			// @todo Add data-ampdevmode attribute to UNFILTERED_HTML_COMMENT_SCRIPT_XPATH here.
			$script_args = $script_sanitizer->get_args();
			if ( $this->args['thread_comments'] && wp_script_is( 'comment-reply', 'done' ) ) {
				$script_args['px_verified_node_xpaths'][] = '//script[ @id = "comment-reply-js" ]';
			}
			if ( current_user_can( 'unfiltered_html' ) ) {
				$script_args['px_verified_node_xpaths'][] = self::UNFILTERED_HTML_COMMENT_SCRIPT_XPATH;
			}

			$script_sanitizer->update_args( $script_args );
		}
	}

	/**
	 * Pre-process the comment form and comment list for AMP.
	 *
	 * @todo Fix https://github.com/ampproject/amp-wp/issues/6231
	 *
	 * @since 0.7
	 */
	public function sanitize() {
		foreach ( $this->dom->getElementsByTagName( Tag::FORM ) as $comment_form ) {
			$action = $comment_form->getAttribute( Attribute::ACTION_XHR );
			if ( ! $action ) {
				$action = $comment_form->getAttribute( Attribute::ACTION );
			}
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
	 * Process comment form.
	 *
	 * @since 0.7
	 *
	 * @param Element $comment_form Comment form.
	 */
	protected function process_comment_form( Element $comment_form ) {
		$this->ampify_threaded_comments( $comment_form );
	}

	/**
	 * Ampify threaded comments by utilizing amp-bind to implement comment reply functionality.
	 *
	 * The logic here is only needed if:
	 * 1. Threaded comments is enabled, and
	 * 2. The comment-reply script was not added to the page.
	 *
	 * @param Element $comment_form Comment form.
	 */
	protected function ampify_threaded_comments( Element $comment_form ) {
		// Do nothing if comment threading is not enabled.
		if ( ! $this->args['thread_comments'] ) {
			return;
		}

		// If comment-reply is on the page, then prevent adding amp-bind implementation as well.
		$comment_reply_script = $this->dom->getElementById( 'comment-reply-js' );
		if ( $comment_reply_script instanceof Element && $this->args['allow_commenting_scripts'] ) {

			// @todo This should add data-px-verified-tag attribute!
			// Improve performance by deferring comment-reply.
			$comment_reply_script->setAttributeNode( $this->dom->createAttribute( 'defer' ) );

			return;
		}

		// Remove comment-reply.js since it will be implemented using amp-bind below.
		if ( $comment_reply_script instanceof Element ) {
			$comment_reply_script->parentNode->removeChild( $comment_reply_script );
		}

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
