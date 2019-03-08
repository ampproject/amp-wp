/**
 * WordPress dependencies
 */
import { Draggable, DropZone } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import BlockPreview from './block-preview';

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

const ReordererItem = ( { page, index, movePageToPosition } ) => {
	const { clientId } = page;
	const pageElementId = `reorder-page-${ clientId }`;
	const transferData = {
		type: 'block',
		srcIndex: index,
		srcClientId: clientId,
	};

	const onDrop = ( event ) => {
		const { srcClientId, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		movePageToPosition( srcClientId, index );
	};

	return (
		<div className="amp-story-reorderer-item">
			<Draggable
				elementId={ pageElementId }
				transferData={ transferData }
			>
				{
					( { onDraggableStart, onDraggableEnd } ) => (
						<Fragment>
							<DropZone onDrop={ onDrop } />
							<div id={ pageElementId }>
								<div
									className="amp-story-page-preview"
									onDragStart={ onDraggableStart }
									onDragEnd={ onDraggableEnd }
									draggable
								>
									<BlockPreview { ...page } />
								</div>
							</div>
						</Fragment>
					)
				}
			</Draggable>
		</div>
	);
};

export default withDispatch( ( dispatch ) => {
	const { movePageToPosition } = dispatch( 'amp/story' );

	return {
		movePageToPosition,
	};
} )( ReordererItem );
