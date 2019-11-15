/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useHistory } from '../../';

function useHistoryReplay( {
	setCurrentPageIndex,
	setPages,
	setSelectedElementIds,
} ) {
	const { state: { replayState } } = useHistory();
	useEffect( () => {
		if ( ! replayState ) {
			return;
		}
		const { currentPageIndex, pages, selectedElementIds } = replayState;
		setCurrentPageIndex( currentPageIndex );
		setPages( pages );
		setSelectedElementIds( selectedElementIds );
	}, [ setCurrentPageIndex, setPages, setSelectedElementIds, replayState ] );
}

export default useHistoryReplay;

