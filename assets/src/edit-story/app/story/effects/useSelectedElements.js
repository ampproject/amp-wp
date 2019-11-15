/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

// If page id is changed, load current page and set page number.
function useSelectedElements( {
	currentPageIndex,
	pages,
	selectedElementIds,
	setSelectedElements,
} ) {
	useEffect( () => {
		if ( typeof currentPageIndex === 'number' && pages.length ) {
			const { elements } = pages[ currentPageIndex ];
			setSelectedElements( elements.filter( ( { id } ) => selectedElementIds.includes( id ) ) );
		}
	}, [ currentPageIndex, pages, selectedElementIds, setSelectedElements ] );
}

export default useSelectedElements;
