/**
 * Internal dependencies
 */
import getPageBlockByName from './getPageBlockByName';

/**
 * Get CTA block.
 *
 * @param {string} pageClientId Root ID.
 * @return {Object} CTA block.
 */
const getCallToActionBlock = ( pageClientId ) => {
	return getPageBlockByName( pageClientId, 'amp/amp-story-cta' );
};

export default getCallToActionBlock;
