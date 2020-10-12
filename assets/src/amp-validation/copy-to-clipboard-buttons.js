/**
 * Internal dependencies
 */
import { getURLValidationTableRows } from './get-url-validation-table-rows';

/**
 * Copies any string to the clipboard.
 *
 * Note: This only works within a callback responding to a user action.
 *
 * @param {string} value Any string value.
 */
function copyToClipboard( value ) {
	const textareaElement = document.createElement( 'textarea' );
	textareaElement.value = value;
	textareaElement.style.position = 'absolute';
	textareaElement.style.left = '-1000%';
	document.body.appendChild( textareaElement );
	textareaElement.select();
	document.execCommand( 'copy' );
	document.body.removeChild( textareaElement );
}

/**
 * Callback when a user clicks a button to copy error details to a clipboard.
 *
 * @param {Event} event Click event.
 */
function handleCopyToClipboardClick( event ) {
	// Handle a single error detail button.
	if ( event.target.classList.contains( 'single-url-detail-copy' ) ) {
		copyToClipboard( event.target.getAttribute( 'data-error-json' ) );
		return;
	}

	// Handle a click on the bulk action button.
	if ( ! event.target.classList.contains( 'copy-all' ) ) {
		return;
	}

	const value = getURLValidationTableRows( { checkedOnly: true } ).map( ( row ) => {
		const copyButton = row.querySelector( '.single-url-detail-copy' );
		if ( ! copyButton ) {
			return null;
		}

		const data = JSON.parse( copyButton.getAttribute( 'data-error-json' ) );

		return data;
	} )
		.filter( ( item ) => item );

	copyToClipboard( JSON.stringify( value, null, '\t' ) );
}

/**
 * Sets up the "Copy to clipboard" buttons on the URL validation screen.
 */
export function handleCopyToClipboardButtons() {
	global.addEventListener( 'click', handleCopyToClipboardClick );
}
