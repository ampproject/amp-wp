/**
 * This function finds the character offset within a certain element.
 *
 * For instance, given the following node with `_` marking the current selection start offset:
 *
 * ```
 * <p>Corgies are the <em>b_est</em>!</p>
 * ```
 *
 * This function would return 17 (`'Corgies are the b'.length`).
 *
 * If optional coordinates are given, the point under the coordinates will be used,
 * otherwise the current on-page selection will be used.
 *
 * This function includes some cross-browser optimization for older browsers even
 * though they aren't really supported by the editor at large (IE).
 *
 * @param {Node} element    DOM node to find current selection within.
 * @param {number} clientX  Optional x coordinate of click.
 * @param {number} clientY  Optional y coordinate of click.
 * @return {number} Current selection start offset as seen in `element` or 0 if not found.
 */
function getCaretCharacterOffsetWithin( element, clientX, clientY ) {
	const doc = element.ownerDocument || element.document;
	const win = doc.defaultView || doc.parentWindow;
	let sel;
	if ( typeof win.getSelection !== 'undefined' ) {
		sel = win.getSelection();
		if ( sel.rangeCount > 0 ) {
			let range = win.getSelection().getRangeAt( 0 );
			if ( clientX && clientY ) {
				if ( doc.caretPositionFromPoint ) {
					range = document.caretPositionFromPoint( clientX, clientY );
				} else if ( doc.caretRangeFromPoint ) {
					range = document.caretRangeFromPoint( clientX, clientY );
				}
			}
			const preCaretRange = range.cloneRange();
			preCaretRange.selectNodeContents( element );
			preCaretRange.setEnd( range.endContainer, range.endOffset );
			return preCaretRange.toString().length;
		}
	}

	sel = doc.selection;
	if ( sel && sel.type !== 'Control' ) {
		const textRange = sel.createRange();
		if ( clientX && clientY ) {
			textRange.moveToPoint( clientX, clientY );
		}
		const preCaretTextRange = doc.body.createTextRange();
		preCaretTextRange.moveToElementText( element );
		preCaretTextRange.setEndPoint( 'EndToEnd', textRange );
		return preCaretTextRange.text.length;
	}

	return 0;
}

export default getCaretCharacterOffsetWithin;
