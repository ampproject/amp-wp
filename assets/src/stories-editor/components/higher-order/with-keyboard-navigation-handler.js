/**
 * WordPress dependencies
 */
import { withDispatch } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { UP, DOWN, RIGHT, LEFT, DELETE } from '@wordpress/keycodes';
/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../../constants';

const applyWithDispatch = withDispatch( ( dispatch, props, { select } ) => {
	const {	isReordering } = select( 'amp/story' );
	const { getSelectedBlock } = select( 'core/block-editor' );
	const { updateBlockAttributes, removeBlock } = dispatch( 'core/block-editor' );

	const onKeyPress = ( event ) => {
		const { keyCode } = event;
		const selectedBlock = getSelectedBlock();
		if(!selectedBlock){
			return;
		}

		let top = 0;
		let left = 0;
		switch(keyCode){
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
			case DELETE:
				removeBlock( selectedBlock.clientId );
				return;
			default:
				return;
		}
		event.preventDefault();
		const newPositionTop = selectedBlock.attributes.positionTop + top;
		const newPositionLeft = selectedBlock.attributes.positionLeft + left;
		updateBlockAttributes( selectedBlock.clientId, { positionTop: newPositionTop, positionLeft: newPositionLeft } );

	};

	return {
		isReordering,
		onKeyPress,
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
			const { name, onKeyPress, isReordering } = props;
			const isPageBlock = 'amp/amp-story-page' === name;

			// Add for page block and inner blocks.
			if ( ! isPageBlock && ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			// Not relevant for reordering.
			if ( isReordering() ) {
				return <BlockEdit { ...props } />;
			}
			return (
				<div className='KeyPress'  onKeyDown={ onKeyPress }>
					<BlockEdit { ...props } />
				</div>
			);
		} );
	},
	'withKeyboardNavigationHandler'
);
