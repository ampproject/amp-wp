/**
 * Internal dependencies
 */
import getPageBlockByName from './getPageBlockByName';
import { ALLOWED_CHILD_BLOCKS } from '../constants';

/**
 * Ensure that only allowed blocks are pasted.
 *
 * @param {[]}      blocks Array of blocks.
 * @param {string}  clientId Page ID.
 * @param {boolean} isFirstPage If is first page.
 * @return {[]} Filtered blocks.
 */
const ensureAllowedBlocksOnPaste = ( blocks, clientId, isFirstPage ) => {
	const allowedBlocks = [];
	blocks.forEach( ( block ) => {
		switch ( block.name ) {
			// Skip copying Page.
			case 'amp/amp-story-page':
				return;
			case 'amp/amp-story-page-attachment':
			case 'amp/amp-story-cta':
				const currentBlock = getPageBlockByName( clientId, block.name );
				if ( currentBlock || ( isFirstPage && block.name === 'amp/amp-story-cta' ) ) {
					return;
				}
				allowedBlocks.push( block );
				break;
			default:
				if ( ALLOWED_CHILD_BLOCKS.includes( block.name ) ) {
					allowedBlocks.push( block );
				}
				break;
		}
	} );
	return allowedBlocks;
};

export default ensureAllowedBlocksOnPaste;
