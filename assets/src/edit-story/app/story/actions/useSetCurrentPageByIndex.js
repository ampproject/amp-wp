/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useSetCurrentPageByIndex( {
	clearSelection,
	setCurrentPageIndex,
} ) {
	const setCurrentPageByIndex = useCallback( ( index ) => {
		clearSelection();
		setCurrentPageIndex( index );
	}, [ clearSelection, setCurrentPageIndex ] );
	return setCurrentPageByIndex;
}

export default useSetCurrentPageByIndex;
