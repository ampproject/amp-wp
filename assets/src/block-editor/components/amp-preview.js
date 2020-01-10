/**
 * External dependencies
 */
import { get } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component, createRef, renderToString } from '@wordpress/element';
import { Icon, IconButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { DotTip } from '@wordpress/nux';
import { compose } from '@wordpress/compose';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import ampBlackIcon from '../../../images/amp-black-icon.svg';
import ampFilledIcon from '../../../images/amp-icon.svg';
import { isAMPEnabled } from '../helpers';
import { POST_PREVIEW_CLASS } from '../constants';

/**
 * Writes the message and graphic in the new preview window that was opened.
 *
 * Forked from the Core component <PostPreviewButton>.
 *
 * @see https://github.com/WordPress/gutenberg/blob/95e769df1f82f6b0ef587d81af65dd2f48cd1c38/packages/editor/src/components/post-preview-button/index.js#L17-L93
 * @param {Document} targetDocument The target document.
 */
function writeInterstitialMessage( targetDocument ) {
	let markup = renderToString(
		<div className="editor-post-preview-button__interstitial-message">
			<Icon
				icon={ ampBlackIcon( { viewBox: '0 0 98 98' } ) }
			/>
			<p>
				{ __( 'Generating AMP preview…', 'amp' ) }
			</p>
		</div>,
	);

	markup += `
		<style>
			body {
				margin: 0;
			}
			.editor-post-preview-button__interstitial-message {
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				height: 100vh;
				width: 100vw;
			}
			@-webkit-keyframes paint {
				0% {
					stroke-dashoffset: 0;
				}
			}
			@-moz-keyframes paint {
				0% {
					stroke-dashoffset: 0;
				}
			}
			@-o-keyframes paint {
				0% {
					stroke-dashoffset: 0;
				}
			}
			@keyframes paint {
				0% {
					stroke-dashoffset: 0;
				}
			}
			.editor-post-preview-button__interstitial-message svg {
				width: 198px;
				height: 198px;
				stroke: #555d66;
				stroke-width: 0.75;
			}
			.editor-post-preview-button__interstitial-message svg .outer,
			.editor-post-preview-button__interstitial-message svg .inner {
				stroke-dasharray: 280;
				stroke-dashoffset: 280;
				-webkit-animation: paint 1.5s ease infinite alternate;
				-moz-animation: paint 1.5s ease infinite alternate;
				-o-animation: paint 1.5s ease infinite alternate;
				animation: paint 1.5s ease infinite alternate;
			}
			p {
				text-align: center;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
		</style>
	`;

	targetDocument.write( markup );
	targetDocument.title = __( 'Generating AMP preview…', 'amp' );
	targetDocument.close();
}

/**
 * A 'Preview AMP' button, forked from the Core 'Preview' button: <PostPreviewButton>.
 *
 * Rendered into the DOM with renderPreviewButton() in helpers/index.js.
 * This also moves the (non-AMP) 'Preview' button to before this, if it's not already there.
 *
 * @see https://github.com/WordPress/gutenberg/blob/95e769df1f82f6b0ef587d81af65dd2f48cd1c38/packages/editor/src/components/post-preview-button/index.js#L95-L200
 */
class AMPPreview extends Component {
	/**
	 * Constructs the class.
	 *
	 * @param {*} args Constructor arguments.
	 */
	constructor( ...args ) {
		super( ...args );
		this.moveButton = this.moveButton.bind( this );
		this.openPreviewWindow = this.openPreviewWindow.bind( this );
		this.buttonRef = createRef();
	}

	/**
	 * Called after the component is updated.
	 *
	 * @param {Object} prevProps The previous props.
	 */
	componentDidUpdate( prevProps ) {
		const { previewLink } = this.props;

		// This relies on the window being responsible to unset itself when
		// navigation occurs or a new preview window is opened, to avoid
		// unintentional forceful redirects.
		if ( previewLink && ! prevProps.previewLink ) {
			this.setPreviewWindowLink( previewLink );
		}

		this.moveButton();
	}

	/**
	 * Moves the (non-AMP) 'Preview' button to before this 'Preview AMP' button, if it's not there already.
	 */
	moveButton() {
		const buttonWrapper = get( this, [ 'buttonRef', 'current', 'parentNode' ], false );
		if ( ! buttonWrapper ) {
			return;
		}

		if ( ! buttonWrapper.previousSibling || ! buttonWrapper.previousSibling.classList.contains( POST_PREVIEW_CLASS ) ) {
			const postPreviewButton = document.querySelector( `.${ POST_PREVIEW_CLASS }` );
			if ( get( postPreviewButton, 'nextSibling' ) ) {
				buttonWrapper.parentNode.insertBefore( buttonWrapper, postPreviewButton.nextSibling );
			}
		}
	}

	/**
	 * Sets the preview window's location to the given URL, if a preview window
	 * exists and is not closed.
	 *
	 * @param {string} url URL to assign as preview window location.
	 */
	setPreviewWindowLink( url ) {
		const { previewWindow } = this;

		if ( previewWindow && ! previewWindow.closed ) {
			previewWindow.location = url;
		}
	}

	/**
	 * Gets the window target.
	 */
	getWindowTarget() {
		const { postId } = this.props;
		return `amp-preview-${ postId }`;
	}

	/**
	 * Opens the preview window.
	 *
	 * @param {Event} event The DOM event.
	 */
	openPreviewWindow( event ) {
		// Our Preview button has its 'href' and 'target' set correctly for a11y
		// purposes. Unfortunately, though, we can't rely on the default 'click'
		// handler since sometimes it incorrectly opens a new tab instead of reusing
		// the existing one.
		// https://github.com/WordPress/gutenberg/pull/8330
		event.preventDefault();

		// Open up a Preview tab if needed. This is where we'll show the preview.
		if ( ! this.previewWindow || this.previewWindow.closed ) {
			this.previewWindow = window.open( '', this.getWindowTarget() );
		}

		// Focus the Preview tab. This might not do anything, depending on the browser's
		// and user's preferences.
		// https://html.spec.whatwg.org/multipage/interaction.html#dom-window-focus
		this.previewWindow.focus();

		// If we don't need to autosave the post before previewing, then we simply
		// load the Preview URL in the Preview tab.
		if ( ! this.props.isAutosaveable ) {
			this.setPreviewWindowLink( event.target.href );
			return;
		}

		// Request an autosave. This happens asynchronously and causes the component
		// to update when finished.
		if ( this.props.isDraft ) {
			this.props.savePost( { isPreview: true } );
		} else {
			this.props.autosave( { isPreview: true } );
		}

		// Display a 'Generating preview' message in the Preview tab while we wait for the
		// autosave to finish.
		writeInterstitialMessage( this.previewWindow.document );
	}

	/**
	 * Renders the component.
	 */
	render() {
		const { previewLink, currentPostLink, errorMessages, isEnabled, isSaveable, isStandardMode } = this.props;

		// Link to the `?preview=true` URL if we have it, since this lets us see
		// changes that were autosaved since the post was last published. Otherwise,
		// just link to the post's URL.
		const href = previewLink || currentPostLink;

		return (
			isEnabled && ! errorMessages.length && ! isStandardMode && (
				<IconButton
					icon={ ampFilledIcon( { viewBox: '0 0 62 62' } ) }
					isLarge
					className="amp-editor-post-preview"
					href={ href }
					label={ __( 'Preview AMP', 'amp' ) }
					target={ this.getWindowTarget() }
					disabled={ ! isSaveable }
					onClick={ this.openPreviewWindow }
					ref={ this.buttonRef }
				>
					<span className="screen-reader-text">
						{
							/* translators: accessibility text */
							__( '(opens in a new tab)', 'amp' )
						}
					</span>
					<DotTip tipId="amp/editor.preview">
						{ __( 'Click “Preview” to load a preview of this page in AMP, so you can make sure you are happy with your blocks.', 'amp' ) }
					</DotTip>
				</IconButton>
			)
		);
	}
}

AMPPreview.propTypes = {
	autosave: PropTypes.func.isRequired,
	currentPostLink: PropTypes.string.isRequired,
	postId: PropTypes.number.isRequired,
	previewLink: PropTypes.string,
	isAutosaveable: PropTypes.bool.isRequired,
	isDraft: PropTypes.bool.isRequired,
	isEnabled: PropTypes.bool.isRequired,
	isSaveable: PropTypes.bool.isRequired,
	savePost: PropTypes.func.isRequired,
	errorMessages: PropTypes.array,
	isStandardMode: PropTypes.bool,
};

export default compose( [
	withSelect( ( select, { forcePreviewLink, forceIsAutosaveable } ) => {
		const {
			getCurrentPostId,
			getCurrentPostAttribute,
			getEditedPostAttribute,
			isEditedPostSaveable,
			isEditedPostAutosaveable,
			getEditedPostPreviewLink,
		} = select( 'core/editor' );

		const {
			getAmpSlug,
			getErrorMessages,
			isStandardMode,
		} = select( 'amp/block-editor' );

		const queryArgs = {};
		queryArgs[ getAmpSlug() ] = 1;
		const initialPreviewLink = getEditedPostPreviewLink();
		const previewLink = initialPreviewLink ? addQueryArgs( initialPreviewLink, queryArgs ) : undefined;

		return {
			postId: getCurrentPostId(),
			currentPostLink: addQueryArgs( getCurrentPostAttribute( 'link' ), queryArgs ),
			previewLink: forcePreviewLink !== undefined ? forcePreviewLink : previewLink,
			isSaveable: isEditedPostSaveable(),
			isAutosaveable: forceIsAutosaveable || isEditedPostAutosaveable(),
			isDraft: [ 'draft', 'auto-draft' ].indexOf( getEditedPostAttribute( 'status' ) ) !== -1,
			isEnabled: isAMPEnabled(),
			errorMessages: getErrorMessages(),
			isStandardMode: isStandardMode(),
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		autosave: dispatch( 'core/editor' ).autosave,
		savePost: dispatch( 'core/editor' ).savePost,
	} ) ),
] )( AMPPreview );
