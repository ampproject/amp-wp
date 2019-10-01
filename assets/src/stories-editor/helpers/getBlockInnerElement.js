/**
 * Internal dependencies
 */
import getBlockDOMNode from './getBlockDOMNode';

/**
 * Returns a movable block's inner element.
 *
 * @param {Object} block Block object.
 *
 * @return {?Element} The inner element.
 */
const getBlockInnerElement = ( block ) => {
	const { name, clientId } = block;
	const isPage = 'amp/amp-story-page' === name;
	const isCTABlock = 'amp/amp-story-cta' === name;

	if ( isPage ) {
		return getBlockDOMNode( clientId );
	}

	if ( isCTABlock ) {
		// Not the block itself is movable, only the button within.
		return document.querySelector( `amp-story-cta-button-${ clientId }` );
	}

	return document.querySelector( `#block-${ clientId }` );
};

export default getBlockInnerElement;
