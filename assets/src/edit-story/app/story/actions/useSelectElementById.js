/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useSelectElementById( {
	setSelectedElementIds,
} ) {
	const selectElementById = useCallback( ( id ) => {
		setSelectedElementIds( [ id ] );
	}, [ setSelectedElementIds ] );
	return selectElementById;
}

export default useSelectElementById;
