/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { Draggable, DropZone } from '@wordpress/components';
import { Fragment, Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';

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

class ReordererItem extends Component {
	constructor() {
		super( ...arguments );

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
	}

	onDrop( event, position ) {
		const { page: { clientId }, movePageToPosition } = this.props;
		const { srcClientId, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const insertIndex = this.getInsertIndex( position );
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
							<Fragment>
								<DropZone
									className={ this.state.isDragging ? 'is-dragging-page' : undefined }
									onDrop={ this.onDrop }
								/>
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
	}
}

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
