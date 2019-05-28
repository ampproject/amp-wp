/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import BlockDraggable from './block-draggable';

export const IconDragHandle = ( { isVisible, className, icon, onDragStart, onDragEnd, blockElementId, clientId } ) => {
	if ( ! isVisible ) {
		return null;
	}

	const dragHandleClassNames = classnames( 'editor-block-mover__control-drag-handle block-editor-block-mover__control-drag-handle', className );

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
						className={ dragHandleClassNames }
						aria-hidden="true"
						onDragStart={ onDraggableStart }
						onDragEnd={ onDraggableEnd }
						draggable
					>
						{ icon }
					</div>
				) }
		</BlockDraggable>
	);
};

IconDragHandle.propTypes = {
	icon: PropTypes.object,
	isVisible: PropTypes.bool,
	className: PropTypes.string,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	blockElementId: PropTypes.string,
	clientId: PropTypes.string,
};

