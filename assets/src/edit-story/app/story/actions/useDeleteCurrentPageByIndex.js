/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useDeleteCurrentPageByIndex( {
	clearSelection,
	setPages,
	setCurrentPageIndex,
} ) {
	const deleteCurrentPageByIndex = useCallback( ( indexToDelete ) => {
		clearSelection();
		setPages( ( pages ) => [
			...pages.slice( 0, indexToDelete ),
			...pages.slice( indexToDelete + 1 )
				.map( ( { index, ...rest } ) => ( { index: index - 1, ...rest } ) ),
		] );
		setCurrentPageIndex( ( currentIndex ) => currentIndex >= indexToDelete ? currentIndex - 1 : currentIndex );
	}, [ clearSelection, setPages, setCurrentPageIndex ] );
	return deleteCurrentPageByIndex;
}

export default useDeleteCurrentPageByIndex;
