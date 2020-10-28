/**
 * External dependencies
 */
import Clipboard from 'clipboard';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getURLValidationTableRows } from './get-url-validation-table-rows';

/**
 * Success handler, called when data is copied to the clipboard.
 *
 * @param {Object} event
 * @param {HTMLElement} event.trigger The element triggering the event.
 */
function onSuccess( { trigger } ) {
	trigger.focus();

	const newInnerText = __( 'Copied to clipboard', 'amp' );

	// Exit if the user has already clicked the button and we are still within the
	// 4000ms before the setTimeout callback runs.
	if ( trigger.innerText === newInnerText ) {
		return;
	}

	const originalText = trigger.innerText;
	trigger.innerText = newInnerText;

	setTimeout( () => {
		if ( document.body.contains( trigger ) ) {
			trigger.innerText = originalText;
		}
	}, 4000 );
}

/**
 * Sets up the "Copy to clipboard" buttons on the URL validation screen.
 */
export function handleCopyToClipboardButtons() {
	const clipboards = [];

	// eslint-disable-next-line no-new
	clipboards.push( new Clipboard( 'button.single-url-detail-copy', {
		text: ( btn ) => {
			const json = JSON.parse( btn.getAttribute( 'data-error-json' ) );
			const statusSelect = btn.closest( 'tr' ).querySelector( '.amp-validation-error-status' );
			json.status = statusSelect.options[ statusSelect.selectedIndex ].text;

			return JSON.stringify( json, null, '\t' );
		},
	} ) );

	// eslint-disable-next-line no-new
	clipboards.push( new Clipboard( 'button.copy-all', {
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
	} ) );

	clipboards.forEach( ( clipboard ) => {
		clipboard.on( 'success', onSuccess );
	} );
}
