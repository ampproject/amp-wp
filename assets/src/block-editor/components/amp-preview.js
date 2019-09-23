/**
 * External dependencies
 */
import { get } from 'lodash';
import { errorMessages } from 'amp-block-editor-data';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component, renderToString } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { DotTip } from '@wordpress/nux';
import { ifCondition, compose } from '@wordpress/compose';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import ampIcon from './amp-black-icon';

/**
 * Writes the message and graphic in the new preview window that was opened.
 *
 * Forked from the Core component <PostPreviewButton>.
 * The errorMessages are imported via wp_localize_script().
 *
 * @see https://github.com/WordPress/gutenberg/blob/95e769df1f82f6b0ef587d81af65dd2f48cd1c38/packages/editor/src/components/post-preview-button/index.js#L17
 * @param {Object} targetDocument The target document.
 */
function writeInterstitialMessage( targetDocument ) {
	let markup = renderToString(
		<div className="editor-post-preview-button__interstitial-message">
			{ ampIcon }
			<p>{ __( 'Generating AMP preview…', 'amp' ) }</p>
		</div>
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
				width: 192px;
				height: 192px;
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
 * Forked from the Core component <PostPreviewButton>.
 *
 * @see https://github.com/WordPress/gutenberg/blob/95e769df1f82f6b0ef587d81af65dd2f48cd1c38/packages/editor/src/components/post-preview-button/index.js
 */
class AMPPreview extends Component {
	/**
	 * Constructs the class.
	 *
	 * @param {*} args Constructor arguments.
	 */
	constructor( ...args ) {
		super( ...args );
		this.openPreviewWindow = this.openPreviewWindow.bind( this );
	}

	/**
	 * Called after the component updated.
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
		}
	}

	/**
	 * Gets the window target.
	 */
	getWindowTarget() {
		const { postId } = this.props;
		return `wp-preview-${ postId }`;
	}

	/**
	 * Opens the preview window.
	 *
	 * @param {Object} event The DOM event.
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
		const { previewLink, currentPostLink, isSaveable } = this.props;

		// Link to the `?preview=true` URL if we have it, since this lets us see
		// changes that were autosaved since the post was last published. Otherwise,
		// just link to the post's URL.
		const href = previewLink || currentPostLink;

		return (
			! errorMessages.length && (
				<PluginPostStatusInfo>
					<Button
						isLarge
						className="editor-post-preview"
						href={ href }
						target={ this.getWindowTarget() }
						disabled={ ! isSaveable }
						onClick={ this.openPreviewWindow }
					>
						{ __( 'AMP Preview', 'amp' ) }
						<span className="screen-reader-text">
							{
								/* translators: accessibility text */
								__( '(opens in a new tab)', 'amp' )
							}
						</span>
						<DotTip tipId="core/editor.preview">
							{ __( 'Click “Preview” to load a preview of this page in AMP, so you can make sure you’re happy with your blocks.', 'amp' ) }
						</DotTip>
					</Button>
				</PluginPostStatusInfo>
			)
		);
	}
}

AMPPreview.propTypes = {
	autosave: PropTypes.bool.isRequired,
	currentPostLink: PropTypes.func.isRequired,
	postId: PropTypes.bool.isRequired,
	previewLink: PropTypes.func.isRequired,
	isAutosaveable: PropTypes.func.isRequired,
	isDraft: PropTypes.func.isRequired,
	isSaveable: PropTypes.func.isRequired,
	isViewable: PropTypes.func.isRequired,
	savePost: PropTypes.bool.isRequired,
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
			getPostType,
		} = select( 'core' );

		const initialPreviewLink = getEditedPostPreviewLink();
		const previewLink = initialPreviewLink ? addQueryArgs( initialPreviewLink, { amp: 1 } ) : undefined;
		const postType = getPostType( getEditedPostAttribute( 'type' ) );

		return {
			postId: getCurrentPostId(),
			currentPostLink: getCurrentPostAttribute( 'link' ),
			previewLink: forcePreviewLink !== undefined ? forcePreviewLink : previewLink,
			isSaveable: isEditedPostSaveable(),
			isAutosaveable: forceIsAutosaveable || isEditedPostAutosaveable(),
			isViewable: get( postType, [ 'viewable' ], false ),
			isDraft: [ 'draft', 'auto-draft' ].indexOf( getEditedPostAttribute( 'status' ) ) !== -1,
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		autosave: dispatch( 'core/editor' ).autosave,
		savePost: dispatch( 'core/editor' ).savePost,
	} ) ),
	ifCondition( ( { isViewable } ) => isViewable ),
] )( AMPPreview );
