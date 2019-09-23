/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Draggable, DropZone } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BlockPreview } from '../';

/**
 * Parses drag & drop events to ensure the event contains valid transfer data.
 *
 * @param {Object} event
 * @return {Object} Parsed event data.
 */
const parseDropEvent = ( event ) => {
	let result = {
		srcRootClientId: null,
		srcClientId: null,
		srcIndex: null,
		type: null,
	};

	if ( ! event.dataTransfer ) {
		return result;
	}

	try {
		result = Object.assign( result, JSON.parse( event.dataTransfer.getData( 'text' ) ) );
	} catch ( err ) {
		return result;
	}

	return result;
};

/**
 * A single item within the list of pages to be reordered.
 *
 * Re-uses existing Draggable and DropZone provided by WordPress
 * in order to not re-invent the wheel.
 */
const ReordererItem = ( { page } ) => {
	const { clientId } = page;

	const index = useSelect( ( select ) => {
		const { getBlockIndex } = select( 'amp/story' );
		return getBlockIndex( clientId );
	}, [ clientId ] );

	const { movePageToPosition } = useDispatch( 'amp/story' );

	const [ isDragging, setIsDragging ] = useState( false );

	const getInsertIndex = ( position ) => {
		if ( index !== undefined ) {
			return position.x === 'right' ? index + 1 : index;
		}

		return undefined;
	};

	/**
	 * onDrop callback.
	 *
	 * @param {Event} event Event object.
	 * @param {{x: number, y: number}} position Item position.
	 */
	const onDrop = ( event, position ) => {
		const { srcClientId, srcIndex, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const positionIndex = getInsertIndex( position );
		const insertIndex = srcIndex < index ? positionIndex - 1 : positionIndex;
		movePageToPosition( srcClientId, insertIndex );
	};

	const pageElementId = `reorder-page-${ clientId }`;
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcClientId: clientId,
	};

	return (
		<div className="amp-story-reorderer-item">
			<Draggable
				elementId={ pageElementId }
				transferData={ transferData }
				onDragStart={ () => setIsDragging( true ) }
				onDragEnd={ () => setIsDragging( false ) }
			>
				{
					( { onDraggableStart, onDraggableEnd } ) => (
						<>
							<DropZone
								className={ isDragging ? 'is-dragging-page' : undefined }
								onDrop={ onDrop }
							/>
							<div
								className="amp-story-reorderer-item-page"
								id={ pageElementId }
							>
								<div
									className="amp-story-page-preview"
									onDragStart={ onDraggableStart }
									onDragEnd={ onDraggableEnd }
									draggable
								>
									<BlockPreview { ...page } />
								</div>
							</div>
						</>
					)
				}
			</Draggable>
		</div>
	);
};

ReordererItem.propTypes = {
	page: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ).isRequired,
	index: PropTypes.number.isRequired,
	movePageToPosition: PropTypes.func.isRequired,
};

export default ReordererItem;
