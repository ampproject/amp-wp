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

		// @todo: This is ugly, but for now necessary fix. This callback is
		// defined recursively: it modifies `pages` object while also
		// depending on it in `useCallback` deps, which occisonally causes nested
		// loops. This should be completely unnecessary with a `useReducer`-based
		// story model.
		const hasChanges = currentPage.elements.reduce( ( acc, element ) => {
			if ( selectedElementIds.includes( element.id ) ) {
				for ( const k in properties ) {
					if ( ! Object.is( properties[ k ], element[ k ] ) ) {
						return true;
					}
				}
			}
			return acc;
		}, false );
		if ( ! hasChanges ) {
			return;
		}

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
