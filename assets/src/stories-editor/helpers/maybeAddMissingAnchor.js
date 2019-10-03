/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import getUniqueId from './getUniqueId';

const {	getBlock } = select( 'core/block-editor' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Add anchor for a block if it's missing.
 *
 * @param {string} clientId Block ID.
 */
const maybeAddMissingAnchor = ( clientId ) => {
	const block = getBlock( clientId );
	if ( ! block ) {
		return;
	}
	if ( ! block.attributes.anchor ) {
		updateBlockAttributes( block.clientId, { anchor: getUniqueId() } );
	}
};

export default maybeAddMissingAnchor;
