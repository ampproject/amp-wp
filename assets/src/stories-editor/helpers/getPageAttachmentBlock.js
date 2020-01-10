/**
 * Internal dependencies
 */
import getPageBlockByName from './getPageBlockByName';

/**
 * Get Page Attachment block.
 *
 * @param {string} pageClientId Root ID.
 * @return {Object} Page Attachment block.
 */
const getPageAttachmentBlock = ( pageClientId ) => {
	return getPageBlockByName( pageClientId, 'amp/amp-story-page-attachment' );
};

export default getPageAttachmentBlock;
