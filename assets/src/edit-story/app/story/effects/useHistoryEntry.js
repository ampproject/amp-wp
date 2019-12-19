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
	story,
	current,
	pages,
	selection,
} ) {
	const { actions: { appendToHistory } } = useHistory();
	useEffect( () => {
		appendToHistory( {
			story,
			current,
			pages,
			selection,
		} );
	}, [ appendToHistory, story, current, pages, selection ] );
}

export default useHistoryEntry;

