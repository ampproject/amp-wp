/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { Draggable, DropZone } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';

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
class ReordererItem extends Component {
	constructor( ...args ) {
		super( ...args );

		this.state = {
			isDragging: false,
		};

		this.onDrop = this.onDrop.bind( this );
	}

	getInsertIndex( position ) {
		const { index } = this.props;

		if ( index !== undefined ) {
			return position.x === 'right' ? index + 1 : index;
		}

		return undefined;
	}

	onDrop( event, position ) {
		const { page: { clientId }, movePageToPosition, index } = this.props;
		const { srcClientId, srcIndex, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const positionIndex = this.getInsertIndex( position );
		const insertIndex = srcIndex < index ? positionIndex - 1 : positionIndex;
		movePageToPosition( srcClientId, insertIndex );
	}

	render() {
		const { page, index } = this.props;
		const { clientId } = page;
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
					onDragStart={ () => this.setState( { isDragging: true } ) }
					onDragEnd={ () => this.setState( { isDragging: false } ) }
				>
					{
						( { onDraggableStart, onDraggableEnd } ) => (
							<>
								<DropZone
									className={ this.state.isDragging ? 'is-dragging-page' : undefined }
									onDrop={ this.onDrop }
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
	}
}

ReordererItem.propTypes = {
	page: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ).isRequired,
	index: PropTypes.number.isRequired,
	movePageToPosition: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select, { page: { clientId } } ) => {
	const { getBlockIndex } = select( 'amp/story' );

	return {
		index: getBlockIndex( clientId ),
	};
} );

const applyWithDispatch = withDispatch( ( dispatch ) => {
	const { movePageToPosition } = dispatch( 'amp/story' );

	return {
		movePageToPosition,
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( ReordererItem );
