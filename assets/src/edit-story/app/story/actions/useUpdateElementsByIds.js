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
function useUpdateElementsByIds( {
	currentPageIndex,
	pages,
	setPages,
} ) {
	const updateElementsByIds = useCallback( ( newElements ) => {
		const currentPage = pages[ currentPageIndex ];
		const newPages = [
			...pages.slice( 0, currentPageIndex ),
			{
				...currentPage,
				elements: [
					...currentPage.elements.map( ( element ) => {
						const { id } = element;
						let properties = {};
						newElements.forEach( ( el ) => {
							if ( el.id === id ) {
								properties = el;
								return false;
							}
							return true;
						} );
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
	return updateElementsByIds;
}

export default useUpdateElementsByIds;
