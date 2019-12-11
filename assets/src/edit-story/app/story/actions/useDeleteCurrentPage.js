/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

// Delete the current Page.
function useDeleteCurrentPage( {
	currentPage,
	pages,
	setPages,
	addBlankPage,
	setCurrentPageIndex,
	currentPageIndex,
} ) {
	const deleteCurrentPage = useCallback( () => {
		// If we have just one page, then let's add a clean Page insead of that.
		// Otherwise, let's remove the page based on the current page's index.
		const newPages = 1 === pages.length ?
			[ addBlankPage() ] :
			pages.filter( ( ( { id } ) => id !== currentPage.id ) );
		// If there's just one page, 0 is the new index, otherwise, the Page before the removed Page.
		const newIndex = 1 === pages.length || 0 === currentPageIndex ?
			0 :
			currentPageIndex - 1;
		setPages( newPages );
		setCurrentPageIndex( newIndex );
		return true;
	}, [ addBlankPage, setPages, pages, currentPage, currentPageIndex, setCurrentPageIndex ] );
	return deleteCurrentPage;
}

export default useDeleteCurrentPage;
