/**
 * Sets input selection to the end for being able to type to the end of the existing text.
 *
 * @param {string} inputSelector Text input selector.
 */
const setInputSelectionToEnd = ( inputSelector ) => {
	const textInput = document.querySelector( inputSelector );
	// Create selection, collapse it in the end of the content.
	if ( textInput ) {
		const range = document.createRange();
		range.selectNodeContents( textInput );
		range.collapse( false );
		const selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange( range );
	}
};

export default setInputSelectionToEnd;
