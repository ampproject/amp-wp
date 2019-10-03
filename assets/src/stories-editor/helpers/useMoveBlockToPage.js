/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { cloneBlock } from '@wordpress/blocks';

/**
 * Hook that exposes functions relevant for moving an element to a neigboring page.
 *
 * @param {string} blockId  Id of block to move
 * @return {Object} Returns two functions: `getPageByOffset` and `moveBlockToPage`.
 */
const useMoveBlockToPage = ( blockId ) => {
	const pages = useSelect( ( select ) => select( 'core/block-editor' ).getBlockOrder(), [] );
	const block = useSelect( ( select ) => select( 'core/block-editor' ).getBlock( blockId ), [ blockId ] );
	const currentPageId = useSelect( ( select ) => select( 'amp/story' ).getCurrentPage(), [] );
	const currentPageIndex = pages.findIndex( ( i ) => i === currentPageId );

	const { setCurrentPage } = useDispatch( 'amp/story' );
	const { selectBlock, removeBlock, insertBlock, updateBlockAttributes } = useDispatch( 'core/block-editor' );

	/**
	 * Get id of neighbor page that is `offset` away from the current page.
	 *
	 * If no page exists in that direction, null will be returned.
	 *
	 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
	 * @return {string} Returns id of target page or null if no page exists there.
	 */
	const getPageByOffset = ( offset ) => {
		const newPageIndex = currentPageIndex + offset;
		const isInsidePageCount = newPageIndex >= 0 && newPageIndex < pages.length;
		const newPageId = pages[ newPageIndex ];

		// Do we even have a neighbor in that direction?
		if ( ! isInsidePageCount ) {
			return null;
		}

		return newPageId;
	};

	/**
	 * Move the element to the given page. Also update the element with the given properties.
	 *
	 * Currently this function removes the old element and creates a clone on the new page.
	 *
	 * @param {string} pageId  Id of page to move element to
	 * @param {Object} attributes  Object with attributes to update on element on new page.
	 */
	const moveBlockToPage = ( pageId, attributes ) => {
		// Remove block and add cloned block to new page.
		removeBlock( blockId );
		const clonedBlock = cloneBlock( block );
		insertBlock( clonedBlock, null, pageId );
		updateBlockAttributes( clonedBlock.clientId, attributes );

		// Switch to new page.
		setCurrentPage( pageId );
		selectBlock( pageId );
	};

	return {
		getPageByOffset,
		moveBlockToPage,
	};
};

export default useMoveBlockToPage;
