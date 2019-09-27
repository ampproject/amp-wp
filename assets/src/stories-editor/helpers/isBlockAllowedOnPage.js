/**
 * WordPress dependencies
 */

import { select } from '@wordpress/data';

const {
	canInsertBlockType,
	getBlockListSettings,
} = select( 'core/block-editor' );

/**
 * Is the given block allowed on the given page?
 *
 * @param {Object}  name The name of the block to test.
 * @param {string}  pageId Page ID.
 * @return {boolean} Returns true if the element is allowed on the page, false otherwise.
 */
export default ( name, pageId ) => {
	// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
	const blockSettings = getBlockListSettings( pageId );
	const isAllowed = canInsertBlockType( name, pageId ) && blockSettings && blockSettings.allowedBlocks.includes( name );
	return Boolean( isAllowed );
};
