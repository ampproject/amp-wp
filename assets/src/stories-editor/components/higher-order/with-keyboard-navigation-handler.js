/**
 * WordPress dependencies
 */
import { withDispatch } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { UP, DOWN, RIGHT, LEFT } from '@wordpress/keycodes';
import { KeyboardShortcuts } from '@wordpress/components';
/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS, ALLOWED_MOVABLE_BLOCKS } from '../../constants';

const applyWithDispatch = withDispatch( ( dispatch, props, { select } ) => {
	const {	isReordering } = select( 'amp/story' );
	const { getSelectedBlock } = select( 'core/block-editor' );
	const { updateBlockAttributes, removeBlock } = dispatch( 'core/block-editor' );
	const selectedBlock = getSelectedBlock();

	const onMoveBlock = ( event ) => {
		const { keyCode, target } = event;
		const { classList } = target;

		if ( ! selectedBlock ) {
			return;
		}

		if ( classList.contains( 'editor-rich-text__editable' ) && ( classList.contains( 'is-selected' ) || classList.contains( 'is-typing' ) ) ) {
			return;
		}

		let top = 0;
		let left = 0;
		switch ( keyCode ) {
			case UP:
				top = -1;
				break;
			case DOWN:
				top = 1;
				break;
			case RIGHT:
				left = 1;
				break;
			case LEFT:
				left = -1;
				break;
			default:
				break;
		}
		event.preventDefault();
		if ( ALLOWED_MOVABLE_BLOCKS.includes( selectedBlock.name ) ) {
			const newPositionTop = selectedBlock.attributes.positionTop + top;
			const newPositionLeft = selectedBlock.attributes.positionLeft + left;
			updateBlockAttributes( selectedBlock.clientId, {
				positionTop: newPositionTop,
				positionLeft: newPositionLeft,
			} );
		}
	};

	const deleteSelectedBlocks = ( event ) => {
		const { target } = event;
		const { classList } = target;
		if ( ! selectedBlock ) {
			return;
		}
		if ( classList.contains( 'editor-rich-text__editable' ) && ( classList.contains( 'is-selected' ) || classList.contains( 'is-typing' ) ) ) {
			return;
		}
		event.preventDefault();
		removeBlock( selectedBlock.clientId );
	};

	return {
		isReordering,
		onMoveBlock,
		deleteSelectedBlocks,
	};
} );

/**
 * Higher-order component that adds right click handler to each inner block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithDispatch( ( props ) => {
			const { name, onMoveBlock, isReordering, deleteSelectedBlocks } = props;
			const isPageBlock = 'amp/amp-story-page' === name;
			// Add for page block and inner blocks.
			if ( ! isPageBlock && ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			// Not relevant for reordering.
			if ( isReordering() ) {
				return <BlockEdit { ...props } />;
			}

			const shortcuts = {
				up: onMoveBlock,
				right: onMoveBlock,
				down: onMoveBlock,
				left: onMoveBlock,
				backspace: deleteSelectedBlocks,
				del: deleteSelectedBlocks,
			};

			return (
				<KeyboardShortcuts shortcuts={ shortcuts } event='keyup' >
					<BlockEdit { ...props } />
				</KeyboardShortcuts>
			);
		} );
	},
	'withKeyboardNavigationHandler'
);
