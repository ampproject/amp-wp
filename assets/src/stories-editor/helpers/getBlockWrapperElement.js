/**
 * Internal dependencies
 */
import isMovableBlock from './isMovableBlock';

/**
 * Returns a movable block's wrapper element.
 *
 * @param {Object} block Block object.
 *
 * @return {null|Element} The inner element.
 */
const getBlockWrapperElement = ( block ) => {
	if ( ! block ) {
		return null;
	}

	const { name, clientId } = block;

	if ( ! isMovableBlock( name ) ) {
		return null;
	}

	return document.querySelector( `.amp-page-child-block[data-block="${ clientId }"]` );
};

export default getBlockWrapperElement;
