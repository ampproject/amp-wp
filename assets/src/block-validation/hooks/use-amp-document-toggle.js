/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Custom hook providing an easy way to toggle AMP and check if it is enabled.
 */
export function useAMPDocumentToggle() {
	const isAMPEnabled = useSelect(
		( select ) => select( 'core/editor' ).getEditedPostAttribute( 'amp_enabled' ) || false,
		[],
	);
	const { editPost } = useDispatch( 'core/editor' );
	const toggleAMP = () => editPost( { amp_enabled: ! isAMPEnabled } );

	return {
		isAMPEnabled,
		toggleAMP,
	};
}
