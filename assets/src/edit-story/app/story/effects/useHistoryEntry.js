/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useHistory } from '../../';

// Record any change to core variables in history (history will know if it's a replay)
function useHistoryEntry( {
	currentPageIndex,
	pages,
	selectedElementIds,
} ) {
	const { actions: { appendToHistory } } = useHistory();
	useEffect( () => {
		appendToHistory( {
			currentPageIndex,
			pages,
			selectedElementIds,
		} );
	}, [ appendToHistory, currentPageIndex, pages, selectedElementIds ] );
}

export default useHistoryEntry;

