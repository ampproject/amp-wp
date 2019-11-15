/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useHistory } from '../../';

function useHistoryReplay( {
	setCurrentPageById,
	setPages,
	setSelectedElementIds,
} ) {
	const { state: { replayState } } = useHistory();
	useEffect( () => {
		if ( ! replayState ) {
			return;
		}
		const { currentPageId, pages, selectedElementIds } = replayState;
		setCurrentPageById( currentPageId );
		setPages( pages );
		setSelectedElementIds( selectedElementIds );
	}, [ setCurrentPageById, setPages, setSelectedElementIds, replayState ] );
}

export default useHistoryReplay;

