/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

const {
	getBlocksByClientId,
	getBlockOrder,
} = select( 'core/block-editor' );

/**
 * Get block by Page ID and block name.
 *
 * @param {string} pageClientId Root ID.
 * @param {string} blockName Block name.
 * @return {Object} Found block.
 */
const getPageBlockByName = ( pageClientId, blockName ) => {
	const innerBlocks = getBlocksByClientId( getBlockOrder( pageClientId ) );
	return innerBlocks.find( ( { name } ) => name === blockName );
};

export default getPageBlockByName;
