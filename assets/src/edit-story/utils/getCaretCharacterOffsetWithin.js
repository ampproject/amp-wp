function getCaretCharacterOffsetWithin( element ) {
	const doc = element.ownerDocument || element.document;
	const win = doc.defaultView || doc.parentWindow;
	let sel;
	if ( typeof win.getSelection !== 'undefined' ) {
		sel = win.getSelection();
		if ( sel.rangeCount > 0 ) {
			const range = win.getSelection().getRangeAt( 0 );
			const preCaretRange = range.cloneRange();
			preCaretRange.selectNodeContents( element );
			preCaretRange.setEnd( range.endContainer, range.endOffset );
			return preCaretRange.toString().length;
		}
	}

	sel = doc.selection;
	if ( sel && sel.type !== 'Control' ) {
		const textRange = sel.createRange();
		const preCaretTextRange = doc.body.createTextRange();
		preCaretTextRange.moveToElementText( element );
		preCaretTextRange.setEndPoint( 'EndToEnd', textRange );
		return preCaretTextRange.text.length;
	}

	return 0;
}

export default getCaretCharacterOffsetWithin;
