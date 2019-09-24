/**
 * This file is mainly copied from core, the only difference is switching to using internal Draggable.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { cloneBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Draggable from './draggable';
import { getCallToActionBlock } from '../../helpers';

const BlockDraggable = ( { children, clientId, blockName, rootClientId, blockElementId, index, onDragStart, onDragEnd, onNeighborDrop, getNeighborPageId } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	// This holds the currently highlighted element
	const currentHoverElement = { pageId: null, current: null };

	/**
	 * Highlight neighboring page on hover. Neigboring page is given by offset.
	 *
	 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
	 */
	const onNeighborHover = ( offset ) => {
		// Get neighboring page.
		const newPageId = getNeighborPageId( offset );
		const hasHighlightChanged = newPageId !== currentHoverElement.pageId;

		if ( ! hasHighlightChanged ) {
			return;
		}

		currentHoverElement.pageId = newPageId;

		// Unhighlight old highlighted page.
		if ( currentHoverElement.current ) {
			currentHoverElement.current.classList.remove( 'amp-page-draggable-hover' );
		}

		// Highlight neigboring page.
		if ( newPageId ) {
			currentHoverElement.current = document.getElementById( `block-${ newPageId }` );
			currentHoverElement.current.classList.add( 'amp-page-draggable-hover' );
		}
	};

	return (
		<Draggable
			blockName={ blockName }
			elementId={ blockElementId }
			transferData={ transferData }
			onDragStart={ onDragStart }
			onDragEnd={ onDragEnd }
			onNeighborDrop={ onNeighborDrop }
			onNeighborHover={ onNeighborHover }
		>
			{
				( { onDraggableStart, onDraggableEnd } ) => {
					return children( {
						onDraggableStart,
						onDraggableEnd,
					} );
				}
			}
		</Draggable>
	);
};

BlockDraggable.propTypes = {
	index: PropTypes.number.isRequired,
	rootClientId: PropTypes.string,
	clientId: PropTypes.string,
	blockElementId: PropTypes.string,
	blockName: PropTypes.string,
	children: PropTypes.any.isRequired,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	onNeighborHover: PropTypes.func,
	onNeighborDrop: PropTypes.func,
	getNeighborPageId: PropTypes.func,
};

export default compose(
	withSelect( ( select, { clientId } ) => {
		const { getBlockIndex, getBlockRootClientId, getBlockOrder, getBlock } = select( 'core/block-editor' );
		const rootClientId = getBlockRootClientId( clientId );
		return {
			index: getBlockIndex( clientId, rootClientId ),
			rootClientId,
			block: getBlock( clientId ),
			getBlockOrder,
		};
	} ),
	withDispatch( ( dispatch, { getBlockOrder, rootClientId, clientId, block, blockName } ) => {
		const { setCurrentPage } = dispatch( 'amp/story' );
		const { selectBlock, removeBlock, insertBlock, updateBlockAttributes } = dispatch( 'core/block-editor' );

		const isCTABlock = 'amp/amp-story-cta' === blockName;

		/**
		 * Is the current element allowed on a given page?
		 *
		 * This is currently only used to filter out instances, where CTA's aren't allowed.
		 *
		 * @param {number} targetPageIndex  Zero-based index of new page
		 * @param {string} targetPageId  Block id of new page
		 * @return {boolean} Returns true if this element is allowed on the given page, false otherwise.
		 */
		const isElementAllowedOnPage = ( targetPageIndex, targetPageId ) => {
			if ( isCTABlock ) {
				// CTA's aren't allowed on the first page
				if ( targetPageIndex === 0 ) {
					return false;
				}

				// CTA's aren't allowed on pages, that already have a CTA
				const ctaBlockOnTargetPage = getCallToActionBlock( targetPageId );
				if ( ctaBlockOnTargetPage !== null ) {
					return false;
				}
			}

			return true;
		};

		/**
		 * Get id of neighbor page that is `offset` away from the current page.
		 *
		 * If no page exists in that direction, if offset is zero or if element is not allowed on that page, null will be returned.
		 *
		 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
		 * @return {string} Returns id of target page or null if element can't be dropped there for any reason.
		 */
		const getNeighborPageId = ( offset ) => {
			const pages = getBlockOrder();
			const currentPageIndex = pages.findIndex( ( i ) => i === rootClientId );
			const newPageIndex = currentPageIndex + offset;
			const isInsidePageCount = newPageIndex >= 0 && newPageIndex < pages.length;
			const newPageId = pages[ newPageIndex ];
			const isAllowedOnPage = isElementAllowedOnPage( newPageIndex, newPageId );

			// Do we even have a legal neighbor in that direction? (offset=0 is not a neighbor)
			if ( offset === 0 || ! isInsidePageCount || ! isAllowedOnPage ) {
				return null;
			}

			return newPageId;
		};

		/**
		 * Drop this element on the page `offset` away from the current page (if possible). If dropped, update the position of the element on the new page.
		 *
		 * Currently this function removes the old element and creates a clone on the new page.
		 *
		 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
		 * @param {Object} newAttributes  Object with attributes to update on element on new page.
		 */
		const onNeighborDrop = ( offset, newAttributes ) => {
			const newPageId = getNeighborPageId( offset );
			if ( ! newPageId ) {
				return;
			}

			// Remove block and add cloned block to new page.
			removeBlock( clientId );
			const clonedBlock = cloneBlock( block );
			insertBlock( clonedBlock, null, newPageId );
			updateBlockAttributes( clonedBlock.clientId, newAttributes );

			// Switch to new page.
			setCurrentPage( newPageId );
			selectBlock( newPageId );
		};

		return {
			onNeighborDrop,
			getNeighborPageId,
		};
	} ),
)( BlockDraggable );

