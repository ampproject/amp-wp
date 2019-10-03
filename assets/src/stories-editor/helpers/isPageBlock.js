/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

const { getBlock } = select( 'core/block-editor' );

/**
 * Check if block is page block.
 *
 * @param {string} clientId Block client ID.
 * @return {boolean} Boolean if block is / is not a page block.
 */
export const isPageBlock = ( clientId ) => {
	const block = getBlock( clientId );
	return block && 'amp/amp-story-page' === block.name;
};

export default isPageBlock;
