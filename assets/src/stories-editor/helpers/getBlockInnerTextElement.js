/**
 * Returns a block's inner element containing the actual text node with its content.
 *
 * @param {Object} block Block object.
 *
 * @return {null|Element} The inner element.
 */
const getBlockInnerTextElement = ( block ) => {
	const { name, clientId } = block;

	switch ( name ) {
		case 'amp/amp-story-text':
			return document.querySelector( `#block-${ clientId } .block-editor-rich-text__editable` );

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const slug = name.replace( '/', '-' );
			return document.querySelector( `#block-${ clientId } .wp-block-${ slug }` );

		default:
			return null;
	}
};

export default getBlockInnerTextElement;
