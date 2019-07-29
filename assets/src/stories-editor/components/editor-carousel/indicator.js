/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Draggable, DropZone, Tooltip } from '@wordpress/components';

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
class Indicator extends Component {
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

	componentDidMount() {
		this.props.setOrder();
	}

	render() {
		const { page, index, currentPage, onClick } = this.props;
		const { clientId } = page;
		const pageElementId = `reorder-page-${ clientId }`;
		const transferData = {
			type: 'block',
			srcIndex: index,
			srcClientId: clientId,
		};

		/* translators: %s: Page number */
		const label = ( pageNumber ) => sprintf( __( 'Page %s', 'amp' ), pageNumber );

		/* translators: %s: Page number */
		const toolTip = ( pageNumber ) => sprintf( __( 'Go to page %s', 'amp' ), pageNumber );
		const className = page.clientId === currentPage ? 'amp-story-editor-carousel-item amp-story-editor-carousel-item--active' : 'amp-story-editor-carousel-item';

		return (
			<div className="amp-story-reorderer-carousel-item">
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
									className={ this.state.isDragging ? 'is-dragging-indicator' : undefined }
									onDrop={ this.onDrop }
								/>
								<div
									key={ page.clientId }
									className={ className }
									onDragStart={ onDraggableStart }
									onDragEnd={ onDraggableEnd }
									draggable
									id={ pageElementId }
								>
									<Tooltip text={ toolTip( index + 1 ) }>
										<Button
											onClick={ ( e ) => {
												e.preventDefault();
												onClick( page.clientId );
											} }
											disabled={ page.clientId === currentPage }
										>
											<span className="screen-reader-text">
												{ label( index + 1 ) }
											</span>
										</Button>
									</Tooltip>
								</div>
							</>
						)
					}
				</Draggable>
			</div>
		);
	}
}

Indicator.propTypes = {
	page: PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ).isRequired,
	movePageToPosition: PropTypes.func.isRequired,
	index: PropTypes.number.isRequired,
	currentPage: PropTypes.string,
	onClick: PropTypes.func.isRequired,
	setOrder: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select, { page: { clientId } } ) => {
	const { getBlockIndex } = select( 'amp/story' );
	const { getBlockOrder } = select( 'core/block-editor' );

	return {
		index: getBlockIndex( clientId ),
		blockOrder: getBlockOrder(),
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, { blockOrder } ) => {
	const { movePageToPosition, setOrder } = dispatch( 'amp/story' );

	return {
		movePageToPosition,
		setOrder: () => {
			setOrder( blockOrder );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( Indicator );
