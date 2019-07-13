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

const handleExport = ( { postId, createErrorNotice, createSuccessNotice } ) => {
	const formData = new FormData();
	const errorMsg = __( 'Could not generate story archive.', 'amp' );

	// Add the form data.
	formData.append( 'action', ampStoriesExport.action );
	formData.append( '_wpnonce', ampStoriesExport.nonce );
	formData.append( 'post_ID', postId );

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
							const a = document.createElement( 'a' );
							const url = URL.createObjectURL( data );
							const clickHandler = () => {
								setTimeout( () => {
									URL.revokeObjectURL( url );
									a.removeEventListener( 'click', clickHandler );
								}, 150 );
							};

							createSuccessNotice( __( 'Generating story archive.', 'amp' ), {
								id: 'amp-story-export__success-snackbar',
								type: 'snackbar',
							} );

							a.addEventListener( 'click', clickHandler, false );
							a.href = url;
							a.download = matches[ 1 ];
							a.click();
						} else {
							createErrorNotice( errorMsg, {
								id: 'amp-story-export__error-notice',
							} );
						}
					} );
			} else {
				// Handle the returned JSON error.
				response.json()
					.then( ( error ) => {
						createErrorNotice( ( error.data && error.data.errorMessage ) ? error.data.errorMessage : errorMsg, {
							id: 'amp-story-export__error-notice',
						} );
					} );
			}
		} );
};

export const name = 'amp-story-export';

/**
 * Renders the actual export menu item.
 *
 * @param {number} postId
 * @param {function} createErrorNotice
 * @param {function} createSuccessNotice
 *
 * @return {Object} The rendered export menu item.
 */
const renderPlugin = ( { postId, createErrorNotice, createSuccessNotice } ) => {
	return (
		<PluginMoreMenuItem
			icon={ 'media-archive' }
			onClick={ () => {
				handleExport( { postId, createErrorNotice, createSuccessNotice } );
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
		const { createErrorNotice, createSuccessNotice } = dispatch( 'core/notices' );

		return {
			createErrorNotice,
			createSuccessNotice,
		};
	} ),
	ifCondition( ( { hasPublishAction } ) => hasPublishAction ),
] )( renderPlugin );
