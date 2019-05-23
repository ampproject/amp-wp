/**
 * Internal dependencies
 */
import BlockDraggable from './block-draggable';

export const BlockDragArea = ( { children, className, onDragStart, onDragEnd, blockElementId, clientId } ) => {
	return (
		<BlockDraggable
			clientId={ clientId }
			blockElementId={ blockElementId }
			onDragStart={ onDragStart }
			onDragEnd={ onDragEnd }
		>
			{
				( { onDraggableStart, onDraggableEnd } ) => (
					<div
						className={ className }
						aria-hidden="true"
						onDragStart={ onDraggableStart }
						onDragEnd={ onDraggableEnd }
						draggable
					>
						{ children }
					</div>
				) }
		</BlockDraggable>
	);
};
