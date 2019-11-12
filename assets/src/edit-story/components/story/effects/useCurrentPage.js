/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

// If page id is changed, load current page and set page number.
function useCurrentPage( currentPageId, pages, setCurrentPage, setCurrentPageNumber ) {
	useEffect( () => {
		if ( currentPageId && pages.length ) {
			const page = pages.find( ( { clientId } ) => clientId === currentPageId );
			setCurrentPage( page );
			setCurrentPageNumber( pages.indexOf( page ) + 1 );
		}
	}, [ currentPageId, pages, setCurrentPage, setCurrentPageNumber ] );
}

export default useCurrentPage;

