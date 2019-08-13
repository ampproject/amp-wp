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
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import Draggable from './draggable';

const BlockDraggable = ( { children, clientId, blockName, rootClientId, blockElementId, index, onDragStart, onDragEnd } ) => {
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	return (
		<Draggable
			blockName={ blockName }
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

BlockDraggable.propTypes = {
	index: PropTypes.number.isRequired,
	rootClientId: PropTypes.string,
	clientId: PropTypes.string,
	blockElementId: PropTypes.string,
	blockName: PropTypes.string,
	children: PropTypes.any.isRequired,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
};

export default withSelect( ( select, { clientId } ) => {
	const { getBlockIndex, getBlockRootClientId } = select( 'core/block-editor' );
	const rootClientId = getBlockRootClientId( clientId );
	return {
		index: getBlockIndex( clientId, rootClientId ),
		rootClientId,
	};
} )( BlockDraggable );
