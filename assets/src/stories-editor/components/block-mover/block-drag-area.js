/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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

BlockDragArea.propTypes = {
	icon: PropTypes.object,
	isVisible: PropTypes.bool,
	className: PropTypes.string,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	blockElementId: PropTypes.string,
	clientId: PropTypes.string,
	children: PropTypes.any.isRequired,
};
