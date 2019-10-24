/**
 * External dependencies
 */
import { every } from 'lodash';
/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS, ALLOWED_MOVABLE_BLOCKS } from '../constants';

const {	getBlock, getBlockRootClientId } = select( 'core/block-editor' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

export const POSITION_TOP_LIMIT = 75;
export const POSITION_TOP_GAP = 10;
export const POSITION_TOP_DEFAULT = 0;

/**
 * Set initial positioning if the selected block is an unmodified block and it has positioned siblings.
 *
 * @param {string} clientId Block ID.
 */
const maybeSetInitialPositioning = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || ! ALLOWED_CHILD_BLOCKS.includes( block.name ) ) {
		return;
	}

	const parentBlock = getBlock( getBlockRootClientId( clientId ) );
	// Short circuit if the top position is already set or the block has no parent.
	if ( 0 !== block.attributes.positionTop || ! parentBlock ) {
		return;
	}

	// Check if it's a new block.
	const newBlock = createBlock( block.name );
	const isUnmodified = every( newBlock.attributes, ( value, key ) => value === block.attributes[ key ] );

	// Only set the position if the block was unmodified before.
	if ( isUnmodified ) {
		const highestPositionTop = parentBlock.innerBlocks
			.filter( ( childBlock ) => ALLOWED_MOVABLE_BLOCKS.includes( childBlock.name ) )
			.map( ( childBlock ) => childBlock.attributes.positionTop )
			.reduce( ( highestTop, positionTop ) => Math.max( highestTop, positionTop ), 0 );

		// If it's more than the limit, set to default.
		const positionTop = highestPositionTop > POSITION_TOP_LIMIT ? POSITION_TOP_DEFAULT : highestPositionTop + POSITION_TOP_GAP;

		updateBlockAttributes( clientId, { positionTop } );
	}
};

export default maybeSetInitialPositioning;
