/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Sets element properties by ID.
 *
 * @param {number}   currentPageIndex Index of the page currently in focus.
 * @param {Array}    pages Array of all pages.
 * @param {Function} setPages Setting the state of pages.
 * @return {Function} Sets properties by element Id.
 */
function useSetPropertiesById( {
   currentPageIndex,
   pages,
   setPages,
} ) {
	const setPropertiesById = useCallback( ( elementId, properties ) => {
		const currentPage = pages[ currentPageIndex ];
		const newPages = [
			...pages.slice( 0, currentPageIndex ),
			{
				...currentPage,
				elements: [
					...currentPage.elements.map( ( element ) => {
						const { id } = element;
						// If ids don't match, let's return the element as it was.
						if ( elementId !== id ) {
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
	}, [ currentPageIndex, pages, setPages ] );
	return setPropertiesById;
}

export default useSetPropertiesById;
