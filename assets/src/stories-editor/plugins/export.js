/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { compose, ifCondition } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const { ampStoriesExport, fetch, FormData, URL } = window;

const handleExport = ( { postId, createErrorNotice, createSuccessNotice, removeNotice } ) => {
	const formData = new FormData();
	const errorMsg = __( 'Could not generate story archive.', 'amp' );

	// Add the form data.
	formData.append( 'action', ampStoriesExport.action );
	formData.append( '_wpnonce', ampStoriesExport.nonce );
	formData.append( 'post_ID', postId );

	const progressNoticeId = 'amp-story-export__success-snackbar';
	const errorNoticeId = 'amp-story-export__error-notice';

	removeNotice( errorNoticeId );
	createSuccessNotice( __( 'Generating story archive…', 'amp' ), {
		id: progressNoticeId,
		type: 'snackbar',
	} );

	/**
	 * Show error notice.
	 *
	 * @param {?Error} error
	 */
	const showErrorNotice = ( error = null ) => {
		removeNotice( progressNoticeId );
		createErrorNotice( error ? error.message : errorMsg, {
			id: errorNoticeId,
		} );
	};

	// Request the export.
	fetch( ampStoriesExport.ajaxUrl, {
		method: 'POST',
		body: formData,
	} )
		.then( ( response ) => {
			if ( response.ok ) {
				// Handle the returned blob data.
				response.blob()
					.then( ( data ) => {
						const header = response.headers.get( 'Content-Disposition' ) || '';
						const matches = header.match( /"(.*?)"/ );

						if ( matches ) {
							// Hide progress notice right before triggering download.
							removeNotice( progressNoticeId );

							const a = document.createElement( 'a' );
							const url = URL.createObjectURL( data );
							const clickHandler = () => {
								setTimeout( () => {
									URL.revokeObjectURL( url );
									a.removeEventListener( 'click', clickHandler );
								}, 150 );
							};

							a.addEventListener( 'click', clickHandler, false );
							a.href = url;
							a.download = matches[ 1 ];
							a.click();
						} else {
							showErrorNotice();
						}
					} )
					.catch( showErrorNotice );
			} else {
				// Handle the returned JSON error.
				response.json()
					.then( ( error ) => {
						showErrorNotice( ( error.data && error.data.errorMessage ) ? new Error( error.data.errorMessage ) : null );
					} )
					.catch( showErrorNotice );
			}
		} ).catch( showErrorNotice );
};

export const name = 'amp-story-export';

/**
 * Renders the actual export menu item.
 *
 * @param {number} postId
 * @param {function} createErrorNotice
 * @param {function} createSuccessNotice
 * @param {function} removeNotice
 *
 * @return {Object} The rendered export menu item.
 */
const renderPlugin = ( { postId, createErrorNotice, createSuccessNotice, removeNotice } ) => {
	return (
		<PluginMoreMenuItem
			icon={ 'media-archive' }
			onClick={ () => {
				handleExport( { postId, createErrorNotice, createSuccessNotice, removeNotice } );
			} }
		>
			{ __( 'Export Story', 'amp' ) }
		</PluginMoreMenuItem>
	);
};

renderPlugin.propTypes = {
	postId: PropTypes.number.isRequired,
	createErrorNotice: PropTypes.func.isRequired,
	createSuccessNotice: PropTypes.func.isRequired,
	removeNotice: PropTypes.func.isRequired,
};

export const render = compose( [
	withSelect( ( select ) => {
		const { getCurrentPost, getCurrentPostId } = select( 'core/editor' );

		return {
			hasPublishAction: get( getCurrentPost(), [ '_links', 'wp:action-publish' ], false ),
			postId: getCurrentPostId(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { createErrorNotice, createSuccessNotice, removeNotice } = dispatch( 'core/notices' );

		return {
			createErrorNotice,
			createSuccessNotice,
			removeNotice,
		};
	} ),
	ifCondition( ( { hasPublishAction } ) => hasPublishAction ),
] )( renderPlugin );
