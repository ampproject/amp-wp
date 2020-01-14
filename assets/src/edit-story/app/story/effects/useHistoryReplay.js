/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useHistory } from '../../';

function useHistoryReplay( {
	restore,
} ) {
	const { state: { replayState } } = useHistory();
	useEffect( () => {
		if ( ! replayState ) {
			return;
		}
		const { current, pages, selection, story } = replayState;
		restore( {
			pages,
			current,
			story,
			selection,
		} );
	}, [ restore, replayState ] );
}

export default useHistoryReplay;

