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
import {
	withSelect,
	dispatch,
} from '@wordpress/data';
import { cloneBlock } from '@wordpress/blocks';
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Draggable from './draggable';
import { getPercentageFromPixels } from '../../helpers';

const BlockDraggable = ( { children, clientId, blockName, rootClientId, blockElementId, block, index, getBlockOrder, onDragStart, onDragEnd } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	const { setCurrentPage } = dispatch( 'amp/story' );
	const { selectBlock, removeBlock, insertBlock, updateBlockAttributes } = dispatch( 'core/block-editor' );

	const getNeighborPageId = ( offset ) => {
		const pages = getBlockOrder();
		const currentPageIndex = pages.findIndex( ( i ) => i === rootClientId );
		const newPageIndex = currentPageIndex + offset;

		// Do we even have a neighbor in that direction? (offset=0 is not a neighbor)
		if ( offset === 0 || newPageIndex < 0 || newPageIndex >= pages.length ) {
			return null;
		}

		return pages[ newPageIndex ];
	};

	const onNeighborDrop = ( offset, finalPosition ) => {
		const newPageId = getNeighborPageId( offset );
		if ( ! newPageId ) {
			return;
		}

		// Remove block and add cloned block to new page.
		removeBlock( clientId );
		const clonedBlock = cloneBlock( block );
		const newAttributes = {
			positionTop: getPercentageFromPixels( 'y', finalPosition.top ),
			positionLeft: getPercentageFromPixels( 'x', finalPosition.left ),
		};
		insertBlock( clonedBlock, null, newPageId );
		updateBlockAttributes( clonedBlock.clientId, newAttributes );

		// Switch to new page.
		setCurrentPage( newPageId );
		selectBlock( newPageId );
	};

	const currentHoverElement = useRef( null );

	const onNeighborHover = ( offset ) => {
		// Unhighlight old highlighted page.
		if ( currentHoverElement.current ) {
			currentHoverElement.current.classList.remove( 'amp-page-draggable-hover' );
		}

		// Highlight neighboring page.
		const newPageId = getNeighborPageId( offset );
		currentHoverElement.current = document.getElementById( `block-${ newPageId }` );
		currentHoverElement.current.classList.add( 'amp-page-draggable-hover' );
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
	block: PropTypes.object,
	getBlockOrder: PropTypes.func,
};

export default withSelect( ( select, { clientId } ) => {
	const { getBlockIndex, getBlockRootClientId, getBlockOrder, getBlock } = select( 'core/block-editor' );
	const rootClientId = getBlockRootClientId( clientId );
	return {
		index: getBlockIndex( clientId, rootClientId ),
		rootClientId,
		block: getBlock( clientId ),
		getBlockOrder,
	};
} )( BlockDraggable );
