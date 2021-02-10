/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, Icon } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { Component, createRef, renderToString } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { isAMPEnabled } from '../helpers';
import ampFilledIcon from '../../../images/amp-icon.svg';
import ampBlackIcon from '../../../images/amp-black-icon.svg';

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
 * @see https://github.com/WordPress/gutenberg/blob/95e769df1f82f6b0ef587d81af65dd2f48cd1c38/packages/editor/src/components/post-preview-button/index.js#L95-L200
 */
class AmpPreviewButton extends Component {
	/**
	 * Constructs the class.
	 *
	 * @param {*} args Constructor arguments.
	 */
	constructor( ...args ) {
		super( ...args );

		this.buttonRef = createRef();
		this.openPreviewWindow = this.openPreviewWindow.bind( this );
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
			if ( this.buttonRef.current ) {
				this.buttonRef.current.focus();
			}
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

		/** @type {HTMLAnchorElement} target */
		const { target } = event;

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
			this.setPreviewWindowLink( target.href );
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

		return isEnabled && ! errorMessages.length && ! isStandardMode && (
			<Button
				className="amp-editor-post-preview"
				href={ href }
				title={ __( 'Preview AMP', 'amp' ) }
				isSecondary
				disabled={ ! isSaveable }
				onClick={ this.openPreviewWindow }
				ref={ this.buttonRef }
			>
				{ ampFilledIcon( { viewBox: '0 0 62 62', width: 18, height: 18 } ) }
				<span className="screen-reader-text">
					{
						/* translators: accessibility text */
						__( '(opens in a new tab)', 'amp' )
					}
				</span>
			</Button>
		);
	}
}

AmpPreviewButton.propTypes = {
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
			getEditedPostAttribute,
			isEditedPostSaveable,
			isEditedPostAutosaveable,
			getEditedPostPreviewLink,
		} = select( 'core/editor' );

		const {
			getAmpUrl,
			getAmpPreviewLink,
			getErrorMessages,
			isStandardMode,
		} = select( 'amp/block-editor' );

		const copyQueryArgs = ( source, destination ) => {
			const sourceUrl = new URL( source );
			const destinationUrl = new URL( destination );
			for ( const [ key, value ] of sourceUrl.searchParams.entries() ) {
				destinationUrl.searchParams.set( key, value );
			}
			return destinationUrl.href;
		};

		const initialPreviewLink = getEditedPostPreviewLink();
		const previewLink = initialPreviewLink ? copyQueryArgs( initialPreviewLink, getAmpPreviewLink() ) : undefined;

		return {
			postId: getCurrentPostId(),
			currentPostLink: getAmpUrl(),
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
] )( AmpPreviewButton );
