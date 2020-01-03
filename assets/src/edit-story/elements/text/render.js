
function TextRender( context, { content, color, fontFamily, fontSize, fontWeight, width } ) {
	// @todo: come up with something A LOT more efficient.
	// @todo: align and wrap text.
	const plainTextDiv = document.createElement( 'div' );
	plainTextDiv.innerHTML = content;
	const plainText = plainTextDiv.textContent;

	// @todo: wait for the font to load.
	// @todo: why is fontSize not a number?
	context.font = `${ fontWeight || 'normal' } ${ fontSize && fontSize !== 'auto' ? fontSize : '10px' } "${ fontFamily || 'sans-serif' }"`;

	context.fillStyle = color || 'black';
	context.fillText( plainText, 0, 0, width );
}

export default TextRender;
