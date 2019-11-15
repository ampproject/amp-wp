/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

// Immutably create new page array after appending element to current page.
function useAppendElementToCurrentPage( {
	currentPageIndex,
	pages,
	setPages,
	setSelectedElementIds,
} ) {
	const appendElementToCurrentPage = useCallback( ( element ) => {
		const currentPage = pages[ currentPageIndex ];
		const newPages = [
			...pages.slice( 0, currentPageIndex ),
			{
				...currentPage,
				elements: [
					...currentPage.elements,
					element,
				],
			},
			...pages.slice( currentPageIndex + 1 ),
		];
		setPages( newPages );
		setSelectedElementIds( [ element.id ] );
		return element;
	}, [ currentPageIndex, pages, setPages, setSelectedElementIds ] );
	return appendElementToCurrentPage;
}

export default useAppendElementToCurrentPage;
