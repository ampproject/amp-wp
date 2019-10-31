/**
 * Copy text to clipboard by using temporary input field.
 *
 * @param {string} text Text to copy.
 */
const copyTextToClipBoard = ( text ) => {
	// Create temporary input element for being able to copy.
	const tmpInput = document.createElement( 'textarea' );
	tmpInput.setAttribute( 'readonly', '' );
	tmpInput.style = {
		position: 'absolute',
		left: '-9999px',
	};
	tmpInput.value = text;
	document.body.appendChild( tmpInput );
	tmpInput.select();
	document.execCommand( 'copy' );
	// Remove the temporary element.
	document.body.removeChild( tmpInput );
};

export default copyTextToClipBoard;
