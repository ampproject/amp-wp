/**
 * External dependencies
 */
import Clipboard from 'clipboard';

/**
 * Internal dependencies
 */
import { getURLValidationTableRows } from './get-url-validation-table-rows';

/**
 * Update the status field ("Kept"/"Removed") because it might have changed.
 *
 * @param {Object} json Parsed JSON object.
 * @param {HTMLButtonElement} button The button with JSON data.
 * @return {Object} Modified JSON object.
 */
function updateJsonStatusField( json, button ) {
	const statusSelect = button.closest( 'tr' ).querySelector( '.amp-validation-error-status' );
	json.status = statusSelect.options[ statusSelect.selectedIndex ].text;

	return json;
}

/**
 * Sets up the "Copy to clipboard" buttons on the URL validation screen.
 */
export function handleCopyToClipboardButtons() {
	/* eslint-disable no-new */
	new Clipboard( 'button.single-url-detail-copy', {
		text: ( btn ) => {
			let json = JSON.parse( btn.getAttribute( 'data-error-json' ) );
			json = updateJsonStatusField( json, btn );

			return JSON.stringify( json, null, '\t' );
		},
	} );

	new Clipboard( 'button.copy-all', {
	/* eslint-enable no-new */
		text: () => {
			const value = getURLValidationTableRows( { checkedOnly: true } ).map( ( row ) => {
				const copyButton = row.querySelector( '.single-url-detail-copy' );
				if ( ! copyButton ) {
					return null;
				}

				let json = JSON.parse( copyButton.getAttribute( 'data-error-json' ) );
				json = updateJsonStatusField( json, copyButton );

				return json;
			} )
				.filter( ( item ) => item );

			return JSON.stringify( value, null, '\t' );
		},
	} );
}
