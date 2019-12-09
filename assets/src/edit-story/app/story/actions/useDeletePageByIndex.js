/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useDeletePageByIndex( {
	clearSelection,
	setPages,
	setCurrentPageIndex,
} ) {
	const deletePageByIndex = useCallback( ( indexToDelete ) => {
		clearSelection();
		setPages( ( pages ) => [
			...pages.slice( 0, indexToDelete ),
			...pages.slice( indexToDelete + 1 ),
		] );
		setCurrentPageIndex( ( currentIndex ) => currentIndex > indexToDelete ? currentIndex - 1 : currentIndex );
	}, [ clearSelection, setPages, setCurrentPageIndex ] );
	return deletePageByIndex;
}

export default useDeletePageByIndex;
