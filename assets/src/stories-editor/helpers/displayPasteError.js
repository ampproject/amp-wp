/**
 * WordPress dependencies
 */
import { getBlockType } from '@wordpress/blocks';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Helper function to display a paste error message
 *
 * @param {string} name Name of block.
 * @param {Function} createErrorNotice Bring in dispatchable createErrorNotice function.
 */
const displayPasteError = ( name, createErrorNotice ) => {
	const blockType = getBlockType( name );
	const removeMessage = sprintf(
		// translators: %s: Type of block (i.e. Text, Image etc)
		__( 'Unable to paste %s block.', 'amp' ),
		blockType.title
	);
	createErrorNotice(
		removeMessage,
		{
			type: 'snackbar',
			isDismissible: true,
		}
	);
};
export default displayPasteError;
