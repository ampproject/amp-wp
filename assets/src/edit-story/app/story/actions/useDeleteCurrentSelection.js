/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

// Delete the elements currently selected.
function useDeleteCurrentSelection( {
	currentPageIndex,
	pages,
	selectedElementIds,
	setPages,
	setSelectedElementIds,
} ) {
	const deleteCurrentSelection = useCallback( () => {
		if ( 0 === selectedElementIds.length ) {
			return false;
		}

		const currentPage = pages[ currentPageIndex ];
		const newPages = [
			...pages.slice( 0, currentPageIndex ),
			{
				...currentPage,
				elements: currentPage.elements.filter( ( { id } ) => ! selectedElementIds.includes( id ) ),
			},
			...pages.slice( currentPageIndex + 1 ),
		];
		setPages( newPages );
		setSelectedElementIds( [] );
		return true;
	}, [ selectedElementIds, setSelectedElementIds, setPages, pages, currentPageIndex ] );
	return deleteCurrentSelection;
}

export default useDeleteCurrentSelection;
