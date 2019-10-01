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
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Draggable from './draggable';
import { isBlockAllowedOnPage } from '../../helpers';

const BlockDraggable = ( { children, clientId, blockName, rootClientId, blockElementId, index, onDragStart, onDragEnd, onNeighborDrop, getNeighborPageId } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	// This holds the currently highlighted element, if any
	const hoverElement = useRef( { pageId: null, element: null, classes: [] } );

	/**
	 * Highlight neighboring page on hover. Neigboring page is given by offset.
	 *
	 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
	 * @param {boolean} clear  Flag used to clear highlighting without making any new highlighting
	 */
	const onNeighborHover = ( offset, clear = false ) => {
		// Get neighboring page.
		const newPageId = clear ? null : getNeighborPageId( offset );
		const hasHighlightChanged = newPageId !== hoverElement.current.pageId;

		if ( ! hasHighlightChanged ) {
			return;
		}

		hoverElement.current.pageId = newPageId;

		// Unhighlight old highlighted page.
		if ( hoverElement.current.element ) {
			hoverElement.current.element.classList.remove( ...hoverElement.current.classes );
		}

		// Highlight neigboring page.
		if ( newPageId ) {
			const isLegal = offset === 0 || isBlockAllowedOnPage( blockName, newPageId );
			const classes = [
				'amp-page-draggable-hover',
				`amp-page-draggable-hover-${ blockName.replace( /\W/g, '-' ) }`,
				isLegal ? 'amp-page-draggable-hover-legal' : 'amp-page-draggable-hover-illegal',
			];
			hoverElement.current.element = document.getElementById( `block-${ newPageId }` );
			hoverElement.current.element.classList.add( ...classes );
			hoverElement.current.classes = classes;
		} else {
			hoverElement.current.element = null;
			hoverElement.current.classes = [];
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
	rootClientId: PropTypes.string.isRequired,
	clientId: PropTypes.string.isRequired,
	blockElementId: PropTypes.string.isRequired,
	blockName: PropTypes.string,
	children: PropTypes.any.isRequired,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	onNeighborDrop: PropTypes.func.isRequired,
	getNeighborPageId: PropTypes.func.isRequired,
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
	withDispatch( ( dispatch, { getBlockOrder, rootClientId, clientId, block } ) => {
		const { setCurrentPage } = dispatch( 'amp/story' );
		const { selectBlock, removeBlock, insertBlock, updateBlockAttributes } = dispatch( 'core/block-editor' );

		/**
		 * Get id of neighbor page that is `offset` away from the current page.
		 *
		 * If no page exists in that direction, null will be returned.
		 *
		 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
		 * @return {string} Returns id of target page or null if no page exists there.
		 */
		const getNeighborPageId = ( offset ) => {
			const pages = getBlockOrder();
			const currentPageIndex = pages.findIndex( ( i ) => i === rootClientId );
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
		 * Drop this element on the page `offset` away from the current page (if possible). If dropped, update the position of the element on the new page.
		 *
		 * Currently this function removes the old element and creates a clone on the new page.
		 *
		 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
		 * @param {Object} newAttributes  Object with attributes to update on element on new page.
		 */
		const onNeighborDrop = ( offset, newAttributes ) => {
			const newPageId = getNeighborPageId( offset );
			const isAllowedOnPage = isBlockAllowedOnPage( block.name, newPageId );
			if ( ! newPageId || ! isAllowedOnPage || ! offset === 0 ) {
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

