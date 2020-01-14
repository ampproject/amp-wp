
function TextContent( { content } ) {
	// @todo: implement a cheaper way to strip markup.
	const buffer = document.createElement( 'div' );
	buffer.innerHTML = content;
	return buffer.textContent;
}

export default TextContent;
