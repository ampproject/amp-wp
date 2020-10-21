/**
 * External dependencies
 */
import Clipboard from 'clipboard';

/**
 * Internal dependencies
 */
import { getURLValidationTableRows } from './get-url-validation-table-rows';

/**
 * Sets up the "Copy to clipboard" buttons on the URL validation screen.
 */
export function handleCopyToClipboardButtons() {
	// eslint-disable-next-line no-new
	new Clipboard( 'button.single-url-detail-copy', {
		text: ( btn ) => {
			const json = JSON.parse( btn.getAttribute( 'data-error-json' ) );
			const statusSelect = btn.closest( 'tr' ).querySelector( '.amp-validation-error-status' );
			json.status = statusSelect.options[ statusSelect.selectedIndex ].text;

			return JSON.stringify( json, null, '\t' );
		},
	} );

	// eslint-disable-next-line no-new
	new Clipboard( 'button.copy-all', {
		text: () => {
			const value = getURLValidationTableRows( { checkedOnly: true } ).map( ( row ) => {
				const copyButton = row.querySelector( '.single-url-detail-copy' );
				if ( ! copyButton ) {
					return null;
				}

				const json = JSON.parse( copyButton.getAttribute( 'data-error-json' ) );
				const statusSelect = row.querySelector( '.amp-validation-error-status' );
				json.status = statusSelect.options[ statusSelect.selectedIndex ].text;

				return json;
			} )
				.filter( ( item ) => item );

			return JSON.stringify( value, null, '\t' );
		},
	} );
}
