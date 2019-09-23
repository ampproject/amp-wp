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

const BlockDraggable = ( { children, clientId, blockName, rootClientId, blockElementId, index, onDragStart, onDragEnd, onNeighborDrop, getNeighborPageId } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	// This holds the currently highlighted element
	const currentHoverElement = { pageId: null, current: null };

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
	block: PropTypes.object,
	getBlockOrder: PropTypes.func,
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

		const getNeighborPageId = ( offset ) => {
			const pages = getBlockOrder();
			const currentPageIndex = pages.findIndex( ( i ) => i === rootClientId );
			const newPageIndex = currentPageIndex + offset;

			const isCTABlock = 'amp/amp-story-cta' === blockName;
			const lowestPageNumberAllowed = isCTABlock ? 1 : 0;
			const highestPageNumberAllowed = pages.length - 1;

			// Do we even have a neighbor in that direction? (offset=0 is not a neighbor)
			if ( offset === 0 || newPageIndex < lowestPageNumberAllowed || newPageIndex > highestPageNumberAllowed ) {
				return null;
			}

			return pages[ newPageIndex ];
		};

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

