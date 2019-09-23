/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, Draggable, DropZone } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
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

const BlockNavigationItem = ( { block, isSelected, onClick, unMovableBlock } ) => {
	const { clientId } = block;

	const [ isDragging, setIsDragging ] = useState( false );

	const rootClientId = useSelect( ( select ) => {
		const { getBlockRootClientId } = select( 'core/block-editor' );
		return getBlockRootClientId( clientId );
	}, [ clientId ] );
	const blockOrder = useSelect( ( select ) => {
		const { getBlockOrder } = select( 'core/block-editor' );
		return getBlockOrder( rootClientId );
	}, [ rootClientId ] );
	const blocks = useSelect( ( select ) => {
		const { getBlocksByClientId } = select( 'core/block-editor' );
		return getBlocksByClientId( blockOrder ).map( ( { clientId: id } ) => id ).reverse();
	}, [ blockOrder ] );

	const { moveBlockToPosition } = useDispatch( 'core/block-editor' );

	const getBlockIndex = ( blockClientId ) => {
		return blocks.indexOf( blockClientId );
	};

	const moveItem = ( item, index ) => {
		// Since the BlockNavigation list is reversed, inserting at index 0 actually means inserting at the end, and vice-versa.
		const reversedIndex = blockOrder.length - 1 - index;

		moveBlockToPosition( item, rootClientId, rootClientId, reversedIndex );
	};

	const getInsertIndex = ( position ) => {
		if ( clientId ) {
			const index = getBlockIndex( clientId );
			return position.y === 'top' ? index : index + 1;
		}

		return undefined;
	};

	const onDrop = ( event, position ) => {
		const { srcClientId, srcIndex, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const dstIndex = getBlockIndex( clientId );
		const positionIndex = getInsertIndex( position );
		const insertIndex = srcIndex < dstIndex ? positionIndex - 1 : positionIndex;
		moveItem( srcClientId, insertIndex );
	};

	const blockElementId = `block-navigation-item-${ clientId }`;

	if ( unMovableBlock ) {
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
				'block-editor-block-navigation__item-is-dragging': isDragging,
			}
		) } >
			<Draggable
				elementId={ blockElementId }
				transferData={ transferData }
				onDragStart={ () => setIsDragging( true ) }
				onDragEnd={ () => setIsDragging( false ) }
			>
				{
					( { onDraggableStart, onDraggableEnd } ) => (
						<>
							<DropZone
								className={ isDragging ? 'is-dragging-block' : undefined }
								onDrop={ onDrop }
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
};

BlockNavigationItem.propTypes = {
	getBlockIndex: PropTypes.func.isRequired,
	moveBlockToPosition: PropTypes.func.isRequired,
	block: PropTypes.shape( {
		name: PropTypes.string.isRequired,
		clientId: PropTypes.string.isRequired,
	} ),
	isSelected: PropTypes.bool,
	onClick: PropTypes.func.isRequired,
	unMovableBlock: PropTypes.object,
};

export default BlockNavigationItem;
