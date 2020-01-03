
function TextRender( context, { content, width, x, y } ) {
	// @todo: load font.
	// @todo: strip HTML or redraw text with bold, etc.
	context.fillStyle = 'black';
	context.fillText( content, x, y, width );
}

export default TextRender;
