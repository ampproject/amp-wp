/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

// Immutably create new page array after appending element to current page.
function useSetPropertiesOnSelectedElements( {
	currentPageIndex,
	pages,
	selectedElementIds,
	setPages,
} ) {
	const setPropertiesOnSelectedElements = useCallback( ( properties ) => {
		const currentPage = pages[ currentPageIndex ];
		const newPages = [
			...pages.slice( 0, currentPageIndex ),
			{
				...currentPage,
				elements: [
					...currentPage.elements.map( ( element ) => {
						const { id } = element;
						if ( ! selectedElementIds.includes( id ) ) {
							return element;
						}
						return {
							...element,
							...properties,
						};
					} ),
				],
			},
			...pages.slice( currentPageIndex + 1 ),
		];
		setPages( newPages );
	}, [ currentPageIndex, pages, selectedElementIds, setPages ] );
	return setPropertiesOnSelectedElements;
}

export default useSetPropertiesOnSelectedElements;
