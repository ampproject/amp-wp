/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Remove deprecated attribute if the block was just migrated.
 *
 * @param {Object} block Block.
 */
const maybeRemoveDeprecatedSetting = ( block ) => {
	if ( ! block ) {
		return;
	}

	const { attributes } = block;

	// If the block was just migrated, update the block to initiate unsaved state.
	if ( attributes.deprecated && 'migrated' === attributes.deprecated ) {
		updateBlockAttributes( block.clientId, {
			deprecated: null,
		} );
	}
};

export default maybeRemoveDeprecatedSetting;
