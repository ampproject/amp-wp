<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @package AMP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\DevMode;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;

/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @internal
 */
class AMP_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Error code for custom inline JS script tag.
	 *
	 * @var string
	 */
	const CUSTOM_INLINE_SCRIPT = 'CUSTOM_INLINE_SCRIPT';

	/**
	 * Error code for custom external JS script tag.
	 *
	 * @var string
	 */
	const CUSTOM_EXTERNAL_SCRIPT = 'CUSTOM_EXTERNAL_SCRIPT';

	/**
	 * Error code for JS event handler attribute.
	 *
	 * @var string
	 */
	const CUSTOM_EVENT_HANDLER_ATTR = 'CUSTOM_EVENT_HANDLER_ATTR';

	/**
	 * Attribute which if present on a `noscript` element will prevent it from being unwrapped.
	 *
	 * @var string
	 */
	const NO_UNWRAP_ATTR = 'data-amp-no-unwrap';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'unwrap_noscripts'      => true,
		'sanitize_js_scripts'   => false,
		'comment_reply_allowed' => 'never', // Can be 'never' , 'always', or 'conditionally'.
	];

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type bool $sanitize_js_scripts Whether to sanitize JS scripts (and not defer for final sanitizer).
	 *      @type bool $unwrap_noscripts    Whether to unwrap noscript elements.
	 * }
	 */
	protected $args;

	/**
	 * Number of scripts which were kept.
	 *
	 * This is used to determine whether noscript elements should be unwrapped.
	 *
	 * @var int
	 */
	protected $kept_script_count = 0;

	/**
	 * Number of kept nodes which were PX-verified.
	 *
	 * @var int
	 */
	protected $px_verified_kept_node_count = 0;

	/**
	 * Sanitizers.
	 *
	 * @var AMP_Base_Sanitizer[]
	 */
	protected $sanitizers = [];

	/**
	 * Init.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {
		parent::init( $sanitizers );

		$this->sanitizers = $sanitizers;
	}

	/**
	 * Sanitize script and noscript elements.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		if ( ! empty( $this->args['sanitize_js_scripts'] ) ) {
			$this->sanitize_js_script_elements();
		}

		// If custom scripts were kept (after sanitize_js_script_elements() ran) it's important that noscripts not be
		// unwrapped or else this could result in the JS and no-JS fallback experiences both being present on the page.
		// So unwrapping is only done when no custom scripts were retained (and the sanitizer arg opts-in to unwrap).
		if ( 0 === $this->kept_script_count && 0 === $this->px_verified_kept_node_count && ! empty( $this->args['unwrap_noscripts'] ) ) {
			$this->unwrap_noscript_elements();
		}

		$sanitizer_arg_updates = [];

		// When there are kept custom scripts, turn off conversion to AMP components since scripts may be attempting to
		// query for them directly, and skip tree shaking since it's likely JS will toggle classes that have associated
		// style rules.
		// @todo There should be an attribute on script tags that opt-in to keeping tree shaking and/or to indicate what class names need to be included.
		if ( $this->kept_script_count > 0 ) {
			$sanitizer_arg_updates[ AMP_Style_Sanitizer::class ]['disable_style_processing'] = true;
			$sanitizer_arg_updates[ AMP_Video_Sanitizer::class ]['native_video_used']        = true;
			$sanitizer_arg_updates[ AMP_Audio_Sanitizer::class ]['native_audio_used']        = true;
			$sanitizer_arg_updates[ AMP_Iframe_Sanitizer::class ]['native_iframe_used']      = true;

			// Once amp-img is deprecated, these won't be needed and an <img> won't prevent strict sandboxing level for valid AMP.
			// Note that AMP_Core_Theme_Sanitizer would have already run, so we can't update it here. Nevertheless,
			// the native_img_used flag was already enabled by the Sandboxing service.
			// @todo We should consider doing this when there are PX-verified scripts as well. This will be the default in AMP eventually anyway, as amp-img is being deprecated.
			$sanitizer_arg_updates[ AMP_Gallery_Block_Sanitizer::class ]['native_img_used'] = true;
			$sanitizer_arg_updates[ AMP_Img_Sanitizer::class ]['native_img_used']           = true;
		}

		// When custom scripts are on the page, use Bento AMP components whenever possible and turn off some CSS
		// processing is unnecessary for a valid AMP page and which can break custom scripts.
		if ( $this->px_verified_kept_node_count > 0 || $this->kept_script_count > 0 ) {
			$sanitizer_arg_updates[ AMP_Tag_And_Attribute_Sanitizer::class ]['prefer_bento']       = true;
			$sanitizer_arg_updates[ AMP_Style_Sanitizer::class ]['transform_important_qualifiers'] = false;
			$sanitizer_arg_updates[ AMP_Style_Sanitizer::class ]['allow_excessive_css']            = true;
			$sanitizer_arg_updates[ AMP_Form_Sanitizer::class ]['native_post_forms_allowed']       = 'always';
		}

		foreach ( $sanitizer_arg_updates as $sanitizer_class => $sanitizer_args ) {
			if ( array_key_exists( $sanitizer_class, $this->sanitizers ) ) {
				$this->sanitizers[ $sanitizer_class ]->update_args( $sanitizer_args );
			}
		}
	}

	/**
	 * Unwrap noscript elements so their contents become the AMP version by default.
	 */
	protected function unwrap_noscript_elements() {
		$noscripts = $this->dom->getElementsByTagName( Tag::NOSCRIPT );

		for ( $i = $noscripts->length - 1; $i >= 0; $i-- ) {
			/** @var Element $noscript */
			$noscript = $noscripts->item( $i );

			// Skip AMP boilerplate.
			if ( $noscript->firstChild instanceof Element && $noscript->firstChild->hasAttribute( Attribute::AMP_BOILERPLATE ) ) {
				continue;
			}

			// Skip unwrapping <noscript> elements that have an opt-out data attribute present.
			if ( $noscript->hasAttribute( self::NO_UNWRAP_ATTR ) ) {
				continue;
			}

			/*
			 * Skip noscript elements inside of amp-img or other AMP components for fallbacks.
			 * See \AMP_Img_Sanitizer::adjust_and_replace_node(). Also skip if the element has dev mode.
			 */
			if ( 'amp-' === substr( $noscript->parentNode->nodeName, 0, 4 ) || DevMode::hasExemptionForNode( $noscript ) ) {
				continue;
			}

			$is_inside_head_el = ( $noscript->parentNode && Tag::HEAD === $noscript->parentNode->nodeName );
			$must_move_to_body = false;

			$fragment = $this->dom->createDocumentFragment();
			$fragment->appendChild( $this->dom->createComment( 'noscript' ) );
			while ( $noscript->firstChild ) {
				if ( $is_inside_head_el && ! $must_move_to_body ) {
					$must_move_to_body = ! $this->dom->isValidHeadNode( $noscript->firstChild );
				}
				$fragment->appendChild( $noscript->firstChild );
			}
			$fragment->appendChild( $this->dom->createComment( '/noscript' ) );

			if ( $must_move_to_body ) {
				$this->dom->body->insertBefore( $fragment, $this->dom->body->firstChild );
				$noscript->parentNode->removeChild( $noscript );
			} else {
				$noscript->parentNode->replaceChild( $fragment, $noscript );
			}

			$this->did_convert_elements = true;
		}
	}

	/**
	 * Sanitize JavaScript script elements.
	 *
	 * This runs explicitly in the script sanitizer before the final validating sanitizer (tag-and-attribute) so that
	 * the style sanitizer will be able to know whether there are custom scripts in the page, and thus whether tree
	 * shaking can be performed.
	 *
	 * @since 2.2
	 */
	protected function sanitize_js_script_elements() {
		// Note that this is looking for type attributes that contain "script" as a normalization of the variations
		// of javascript, ecmascript, jscript, and livescript. This could also end up matching MIME types such as
		// application/postscript or text/vbscript, but such scripts are either unlikely to be the source of a script
		// tag or else they would be extremely rare (and would be invalid AMP anyway).
		// See <https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types#textjavascript>.
		$scripts = $this->dom->xpath->query(
			'
				//script[
					not( @type )
					or
					@type = "module"
					or
					contains( @type, "script" )
				]'
		);

		$comment_reply_script = null;

		/** @var Element $script */
		foreach ( $scripts as $script ) {
			if ( DevMode::hasExemptionForNode( $script ) ) { // @todo Should this also skip when AMP-unvalidated?
				continue;
			}

			if ( ValidationExemption::is_px_verified_for_node( $script ) ) {
				$this->px_verified_kept_node_count++;
				// @todo Consider forcing any PX-verified script to have async/defer if not module. For inline scripts, hack via data: URL?
				continue;
			}

			if ( $script->hasAttribute( Attribute::SRC ) ) {
				// Skip external AMP CDN scripts.
				if ( 0 === strpos( $script->getAttribute( Attribute::SRC ), 'https://cdn.ampproject.org/' ) ) {
					continue;
				}

				// Defer consideration of commenting scripts until we've seen what other scripts are kept on the page.
				if ( $script->getAttribute( Attribute::ID ) === 'comment-reply-js' ) {
					$comment_reply_script = $script;
					continue;
				}

				$removed = $this->remove_invalid_child(
					$script,
					[ 'code' => self::CUSTOM_EXTERNAL_SCRIPT ]
				);
				if ( ! $removed ) {
					$this->kept_script_count++;
				}
			} else {
				// Skip inline scripts used by AMP.
				if ( $script->hasAttribute( Attribute::AMP_ONERROR ) ) {
					continue;
				}

				// As a special case, mark the script output by wp_comment_form_unfiltered_html_nonce() as being in dev-mode
				// since it is output when the user is authenticated (when they can unfiltered_html), and since it has no
				// impact on PX we can just ignore it.
				if (
					$script->previousSibling instanceof Element
					&&
					Tag::INPUT === $script->previousSibling->tagName
					&&
					$script->previousSibling->getAttribute( Attribute::NAME ) === '_wp_unfiltered_html_comment_disabled'
				) {
					$script->setAttributeNode( $this->dom->createAttribute( DevMode::DEV_MODE_ATTRIBUTE ) );
					continue;
				}

				$removed = $this->remove_invalid_child(
					$script,
					[ 'code' => self::CUSTOM_INLINE_SCRIPT ]
				);
				if ( ! $removed ) {
					$this->kept_script_count++;
				}
			}
		}

		$event_handler_attributes = $this->dom->xpath->query(
			'
				//@*[
					substring(name(), 1, 2) = "on"
					and
					name() != "on"
				]
			'
		);
		/** @var DOMAttr $event_handler_attribute */
		foreach ( $event_handler_attributes as $event_handler_attribute ) {
			/** @var Element $element */
			$element = $event_handler_attribute->parentNode;
			if (
				DevMode::hasExemptionForNode( $element )
				||
				(
					Extension::POSITION_OBSERVER === $element->tagName
					&&
					Attribute::ONCE === $event_handler_attribute->nodeName
				)
				||
				(
					Extension::FONT === $element->tagName
					&&
					substr( $event_handler_attribute->nodeName, 0, 3 ) === 'on-'
				)
			) {
				continue;
			}

			// Since the attribute has been PX-verified, move along.
			if ( ValidationExemption::is_px_verified_for_node( $event_handler_attribute ) ) {
				$this->px_verified_kept_node_count++;
				continue;
			}

			$removed = $this->remove_invalid_attribute(
				$element,
				$event_handler_attribute,
				[ 'code' => self::CUSTOM_EVENT_HANDLER_ATTR ]
			);
			if ( ! $removed ) {
				$this->kept_script_count++;
			}
		}

		// Handle the comment-reply script, removing it if it's not never allowed, marking it as PX-verified if it is
		// always allowed, or leaving it alone if it is 'conditionally' allowed since it will be dealt with later
		// in the AMP_Comments_Sanitizer.
		if ( $comment_reply_script ) {
			if ( 'never' === $this->args['comment_reply_allowed'] ) {
				$comment_reply_script->parentNode->removeChild( $comment_reply_script );
			} elseif ( 'always' === $this->args['comment_reply_allowed'] ) {
				// Prevent the comment-reply script from being removed later in the comments sanitizer.
				ValidationExemption::mark_node_as_px_verified( $comment_reply_script );
			}
		}
	}
}
