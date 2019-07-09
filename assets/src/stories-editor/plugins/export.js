/**
 * WordPress dependencies
 */
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { select, dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const { ampStoriesExport, fetch, FormData, URL } = window;
const { getCurrentPostId } = select( 'core/editor' );
const { createErrorNotice, createSuccessNotice } = dispatch( 'core/notices' );

const handleExport = () => {
	const formData = new FormData();
	const errorMsg = __( 'Could not generate the AMP story archive.', 'amp' );

	// Add the form data.
	formData.append( 'action', ampStoriesExport.action );
	formData.append( '_wpnonce', ampStoriesExport.nonce );
	formData.append( 'post_ID', getCurrentPostId() );

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

							createSuccessNotice( __( 'Generating AMP Story archive.', 'amp' ), {
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

export const render = () => (
	<PluginMoreMenuItem
		icon={ 'media-archive' }
		onClick={ handleExport }
	>
		{ __( 'AMP Story Export', 'amp' ) }
	</PluginMoreMenuItem>
);
