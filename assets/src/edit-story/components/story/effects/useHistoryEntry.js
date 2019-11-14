/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useHistory from '../../history';

// Record any change to core variables in history (history will know if it's a replay)
function useHistoryEntry( {
	currentPageId,
	pages,
	selectedElementIds,
} ) {
	const { actions: { appendToHistory } } = useHistory();
	useEffect( () => {
		appendToHistory( {
			currentPageId,
			pages,
			selectedElementIds,
		} );
	}, [ appendToHistory, currentPageId, pages, selectedElementIds ] );
}

export default useHistoryEntry;

