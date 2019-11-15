/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

// If page id is changed, load current page and set page number.
function useClearSelection( {
	selectedElementIds,
	setSelectedElementIds,
} ) {
	const clearSelection = useCallback( () => {
		if ( selectedElementIds.length === 0 ) {
			return false;
		}
		setSelectedElementIds( [] );
		return true;
	}, [ selectedElementIds, setSelectedElementIds ] );
	return clearSelection;
}

export default useClearSelection;
