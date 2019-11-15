/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

// If page id is changed, load current page and set page number.
function useCurrentPage( {
	currentPageIndex,
	pages,
	setCurrentPage,
	setCurrentPageNumber,
} ) {
	useEffect( () => {
		if ( typeof currentPageIndex === 'number' && pages.length ) {
			const page = pages[ currentPageIndex ];
			setCurrentPage( page );
			setCurrentPageNumber( currentPageIndex + 1 );
		}
	}, [ currentPageIndex, pages, setCurrentPage, setCurrentPageNumber ] );
}

export default useCurrentPage;
