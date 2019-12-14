/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ALLOWED_TOP_LEVEL_BLOCKS, MEDIA_INNER_BLOCKS } from '../constants';

const {	getBlock, getBlocksByClientId, getBlockOrder } = select( 'core/block-editor' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Verify and perhaps update autoAdvanceAfterMedia attribute for pages.
 *
 * For pages with autoAdvanceAfter set to 'media',
 * verify that the referenced media block still exists.
 * If not, find another media block to be used for the
 * autoAdvanceAfterMedia attribute.
 *
 * @param {string} clientId Block ID.
 */
const maybeUpdateAutoAdvanceAfterMedia = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block || ! ALLOWED_TOP_LEVEL_BLOCKS.includes( block.name ) ) {
		return;
	}

	if ( 'media' !== block.attributes.autoAdvanceAfter ) {
		return;
	}

	const innerBlocks = getBlocksByClientId( getBlockOrder( clientId ) );

	const mediaBlock = block.attributes.autoAdvanceAfterMedia && innerBlocks.find( ( { attributes } ) => attributes.anchor === block.attributes.autoAdvanceAfterMedia );

	if ( mediaBlock ) {
		return;
	}

	const firstMediaBlock = innerBlocks.find( ( { name } ) => MEDIA_INNER_BLOCKS.includes( name ) );
	const autoAdvanceAfterMedia = firstMediaBlock ? firstMediaBlock.attributes.anchor : '';

	if ( block.attributes.autoAdvanceAfterMedia !== autoAdvanceAfterMedia ) {
		updateBlockAttributes( clientId, { autoAdvanceAfterMedia } );
	}
};

export default maybeUpdateAutoAdvanceAfterMedia;
