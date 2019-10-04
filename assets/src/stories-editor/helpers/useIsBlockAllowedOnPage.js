/**
 * WordPress dependencies
 */

import { useSelect } from '@wordpress/data';

/**
 * A hook to be used to determine if a given block type is allowed on a given page?
 *
 * @return {Function} Returns a function to determine if the given block type can be dropped on a given page.
 */
const useIsBlockAllowedOnPage = () => {
	const { getBlockListSettings, canInsertBlockType } = useSelect( ( select ) => select( 'core/block-editor' ), [] );

	/**
	 * Is the element allowed on the given page?
	 *
	 * @param {Object}  blockName The name of the block to test.
	 * @param {string}  pageId Page ID.
	 * @return {boolean} Returns true if the element is allowed on the page, false otherwise.
	 */
	return ( blockName, pageId ) => {
		// canInsertBlockType() alone is not enough, see https://github.com/WordPress/gutenberg/issues/14515
		const blockSettings = getBlockListSettings( pageId );
		const canInsert = canInsertBlockType( blockName, pageId );
		const isAllowed = canInsert && blockSettings && blockSettings.allowedBlocks.includes( blockName );
		return Boolean( isAllowed );
	};
};

export default useIsBlockAllowedOnPage;
