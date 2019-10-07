/**
 * External dependencies
 */
import { get } from 'lodash';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const { ampStoriesExport, fetch, FormData, URL } = window;

export const name = 'amp-story-export';

export const icon = 'media-archive';

/**
 * Renders the actual export menu item.
 *
 * @return {ReactElement} The rendered export menu item.
 */
const StoryExport = () => {
	const { createErrorNotice, createSuccessNotice, removeNotice } = useDispatch( 'core/notices' );

	const {
		hasPublishAction,
		postId,
	} =	useSelect( ( select ) => {
		const { getCurrentPost, getCurrentPostId } = select( 'core/editor' );

		return {
			hasPublishAction: get( getCurrentPost(), [ '_links', 'wp:action-publish' ], false ),
			postId: getCurrentPostId(),
		};
	} );

	if ( ! hasPublishAction ) {
		return null;
	}

	const progressNoticeId = 'amp-story-export__success-snackbar';
	const errorNoticeId = 'amp-story-export__error-notice';

	const errorMsg = __( 'Could not generate story archive.', 'amp' );

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

	const onClick = () => {
		const formData = new FormData();

		// Add the form data.
		formData.append( 'action', ampStoriesExport.action );
		formData.append( '_wpnonce', ampStoriesExport.nonce );
		formData.append( 'post_ID', postId );

		removeNotice( errorNoticeId );
		createSuccessNotice( __( 'Generating story archive…', 'amp' ), {
			id: progressNoticeId,
			type: 'snackbar',
		} );

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

	return (
		<PluginMoreMenuItem
			onClick={ onClick }
		>
			{ __( 'Export Story', 'amp' ) }
		</PluginMoreMenuItem>
	);
};

export const render = () => {
	return (
		<StoryExport />
	);
};
