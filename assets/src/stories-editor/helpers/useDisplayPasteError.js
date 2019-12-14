/**
 * WordPress dependencies
 */
import { getBlockType } from '@wordpress/blocks';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';

/**
 * A hook to display a paste error message.
 *
 * @return {Function} Returns a function to display a paste error message.
 */
const useDisplayPasteError = () => {
	const { createErrorNotice } = useDispatch( 'core/notices' );

	return ( name ) => {
		const blockType = getBlockType( name );
		const removeMessage = sprintf(
			// translators: %s: Type of block (i.e. Text, Image etc)
			__( 'Unable to paste %s block.', 'amp' ),
			blockType.title,
		);
		createErrorNotice(
			removeMessage,
			{
				type: 'snackbar',
				isDismissible: true,
			},
		);
	};
};

export default useDisplayPasteError;
