/**
 * This file is mainly copied from core, the only difference is switching to using internal Draggable.
 */

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';

/*
 * Internal dependencies.
 */
import Draggable from './draggable';

const BlockDraggable = ( { children, clientId, rootClientId, blockElementId, index, onDragStart, onDragEnd } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	return (
		<Draggable
			elementId={ blockElementId }
			transferData={ transferData }
			onDragStart={ onDragStart }
			onDragEnd={ onDragEnd }
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

export default withSelect( ( select, { clientId } ) => {
	const { getBlockIndex, getBlockRootClientId } = select( 'core/block-editor' );
	const rootClientId = getBlockRootClientId( clientId );
	return {
		index: getBlockIndex( clientId, rootClientId ),
		rootClientId,
	};
} )( BlockDraggable );
