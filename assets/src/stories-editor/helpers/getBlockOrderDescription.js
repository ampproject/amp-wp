/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Return a label for the block order controls depending on block position.
 *
 * @param {string}  type            Block type - in the case of a single block, should define its 'type'. I.e. 'Text', 'Heading', 'Image' etc.
 * @param {number}  currentPosition The block's current position.
 * @param {number}  newPosition     The block's new position.
 * @param {boolean} isFirst         This is the first block.
 * @param {boolean} isLast          This is the last block.
 * @param {number}  dir             Direction of movement (> 0 is considered to be going down, < 0 is up).
 *
 * @return {string} Label for the block movement controls.
 */
const getBlockOrderDescription = ( type, currentPosition, newPosition, isFirst, isLast, dir ) => {
	if ( isFirst && isLast ) {
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is the only block, and cannot be moved', 'amp' ), type );
	}

	if ( dir > 0 && ! isLast ) {
		// moving down
		return sprintf(
			// translators: 1: Type of block (i.e. Text, Image etc), 2: Position of selected block, 3: New position
			__( 'Move %1$s block from position %2$d down to position %3$d', 'amp' ),
			type,
			currentPosition,
			newPosition,
		);
	}

	if ( dir > 0 && isLast ) {
		// moving down, and is the last item
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is at the end of the content and can’t be moved down', 'amp' ), type );
	}

	if ( dir < 0 && ! isFirst ) {
		// moving up
		return sprintf(
			// translators: 1: Type of block (i.e. Text, Image etc), 2: Position of selected block, 3: New position
			__( 'Move %1$s block from position %2$d up to position %3$d', 'amp' ),
			type,
			currentPosition,
			newPosition,
		);
	}

	if ( dir < 0 && isFirst ) {
		// moving up, and is the first item
		// translators: %s: Type of block (i.e. Text, Image etc)
		return sprintf( __( 'Block %s is at the beginning of the content and can’t be moved up', 'amp' ), type );
	}

	return undefined;
};

export default getBlockOrderDescription;
