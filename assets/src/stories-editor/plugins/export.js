/* global fetch, alert */

/**
 * WordPress dependencies
 */
import { PluginMoreMenuItem } from '@wordpress/edit-post';
const { ampStoriesExport, FormData, URL } = window;

const handleExport = () => {
	const fromData = new FormData();

	// Add the form data.
	fromData.append( 'action', ampStoriesExport.action );
	fromData.append( '_wpnonce', ampStoriesExport.nonce );
	fromData.append( 'post_ID', document.getElementById( 'post_ID' ).value );

	// Request the export.
	fetch( ampStoriesExport.ajaxUrl, {
		method: 'POST',
		body: fromData,
	} )
		.then( ( response ) => {
			if ( response.ok ) {
				// Handle the returned blob data.
				response.blob()
					.then( ( data ) => {
						const matches = response.headers.get( 'Content-Disposition' ).match( /"(.*?)"/ );
						if ( matches ) {
							const a = document.createElement( 'a' );
							a.href = URL.createObjectURL( data );
							a.download = matches[ 1 ];
							document.body.appendChild( a );
							a.click();
							a.remove();
						}
					} );
			} else {
				// Handle the returned JSON error.
				response.json()
					.then( ( error ) => {
						// @todo should we show this in a different way?
						alert( error.data.errorMessage ); // eslint-disable-line no-alert
					} );
			}
		} );
};

export const name = 'amp-story-export';

export const render = () => (
	<PluginMoreMenuItem
		icon={ ampStoriesExport.icon }
		onClick={ handleExport }
	>
		{ ampStoriesExport.label }
	</PluginMoreMenuItem>
);
