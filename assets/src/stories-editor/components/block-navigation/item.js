/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { Button, Draggable, DropZone } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { BlockPreviewLabel } from '../';

/**
 * Parses drag & drop events to ensure the event contains valid transfer data.
 *
 * @param {Object} event
 * @return {Object} Parsed event data.
 */
const parseDropEvent = ( event ) => {
	let result = {
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

class BlockNavigationItem extends Component {
	constructor( ...args ) {
		super( ...args );

		this.state = {
			isDragging: false,
		};

		this.onDrop = this.onDrop.bind( this );
	}

	getInsertIndex( position ) {
		const { block: { clientId }, getBlockIndex } = this.props;

		if ( clientId !== undefined ) {
			const index = getBlockIndex( clientId );
			return position.y === 'top' ? index : index + 1;
		}

		return undefined;
	}

	onDrop( event, position ) {
		const { block: { clientId }, moveBlockToPosition, getBlockIndex } = this.props;
		const { srcClientId, srcIndex, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const dstIndex = getBlockIndex( clientId );
		const positionIndex = this.getInsertIndex( position );
		const insertIndex = srcIndex < dstIndex ? positionIndex - 1 : positionIndex;
		moveBlockToPosition( srcClientId, insertIndex );
	}

	render() {
		const { block, getBlockIndex, isSelected, onClick } = this.props;
		const isCallToActionBlock = 'amp/amp-story-cta' === block.name;
		const { clientId } = block;
		const blockElementId = `block-navigation-item-${ clientId }`;

		if ( isCallToActionBlock ) {
			return (
				<div className="editor-block-navigation__item block-editor-block-navigation__item">
					<Button
						className={ classnames(
							'components-button editor-block-navigation__item-button block-editor-block-navigation__item-button',
							{
								'is-selected': isSelected,
							}
						) }
						onClick={ onClick }
						id={ blockElementId }
					>
						<BlockPreviewLabel
							block={ block }
							accessibilityText={ isSelected && __( '(selected block)', 'amp' ) }
						/>
					</Button>
				</div>
			);
		}

		const transferData = {
			type: 'block',
			srcIndex: getBlockIndex( clientId ),
			srcClientId: clientId,
		};

		return (
			<div className={ classnames(
				'editor-block-navigation__item block-editor-block-navigation__item',
				{
					'block-editor-block-navigation__item-is-dragging': this.state.isDragging,
				}
			) } >
				<Draggable
					elementId={ blockElementId }
					transferData={ transferData }
					onDragStart={ () => this.setState( { isDragging: true } ) }
					onDragEnd={ () => this.setState( { isDragging: false } ) }
				>
					{
						( { onDraggableStart, onDraggableEnd } ) => (
							<>
								<DropZone
									className={ this.state.isDragging ? 'is-dragging-block' : undefined }
									onDrop={ this.onDrop }
								/>
								<div className="block-navigation__placeholder"></div>
								<Button
									className={ classnames(
										'components-button editor-block-navigation__item-button block-editor-block-navigation__item-button',
										{
											'is-selected': isSelected,
										}
									) }
									onClick={ onClick }
									id={ blockElementId }
									onDragStart={ onDraggableStart }
									onDragEnd={ onDraggableEnd }
									draggable
								>
									<BlockPreviewLabel
										block={ block }
										accessibilityText={ isSelected && __( '(selected block)', 'amp' ) }
									/>
								</Button>
							</>
						)
					}
				</Draggable>
			</div>
		);
	}
}

BlockNavigationItem.propTypes = {
	getBlockIndex: PropTypes.func.isRequired,
	moveBlockToPosition: PropTypes.func.isRequired,
	block: PropTypes.shape( {
		name: PropTypes.string.isRequired,
		clientId: PropTypes.string.isRequired,
	} ),
	isSelected: PropTypes.bool,
	onClick: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select, { block: { clientId } } ) => {
	const { getBlockOrder, getBlockRootClientId, getBlocksByClientId } = select( 'core/block-editor' );

	const blockOrder = getBlockOrder( getBlockRootClientId( clientId ) );

	// Need to reverse the list just like BlockNavigation does.
	const blocks = getBlocksByClientId( blockOrder ).map( ( { clientId: id } ) => id ).reverse();

	return {
		getBlockIndex: ( blockClientId ) => {
			return blocks.indexOf( blockClientId );
		},
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, { block: { clientId } }, { select } ) => {
	const { getBlockOrder, getBlockRootClientId } = select( 'core/block-editor' );
	const { moveBlockToPosition } = dispatch( 'core/block-editor' );

	const rootClientId = getBlockRootClientId( clientId );
	const blockOrder = getBlockOrder( rootClientId );

	return {
		moveBlockToPosition: ( block, index ) => {
			// Since the BlockNavigation list is reversed, inserting at index 0 actually means inserting at the end, and vice-versa.
			const reversedIndex = blockOrder.length - 1 - index;

			moveBlockToPosition( block, rootClientId, rootClientId, reversedIndex );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( BlockNavigationItem );
