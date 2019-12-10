/**
 * Internal dependencies
 */
import { isInsideRange } from './utils';

/**
 * Add elements to given page. If no page given, add elements to current page.
 *
 * If page index is outside bounds of available pages, nothing happens.
 *
 * Elements are expected to a be list of element objects with at least an id property.
 * No validation is made on these objects. If elements aren't a list, nothing happens.
 *
 * Elements will be added to the front (end) of the list of elements on the current page.
 *
 * If elements are added to the current page, selection is set to be exactly the new elements.
 * Otherwise, selection is unchanged.
 *
 * Current page is unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Page to insert elements on or null if current page should be used
 * @param {Array.<Object>} payload.elements Elements to insert on the given page.
 * @return {Object} New state
 */
function addElements( state, { pageIndex, elements } ) {
	const indexToAddTo = pageIndex === null ? state.current : pageIndex;
	const isInsertingOnCurrentPage = indexToAddTo === state.current;

	const isWithinBounds = isInsideRange( indexToAddTo, 0, state.pages.length - 1 );
	if ( isWithinBounds || ! Array.isArray( elements ) ) {
		return state;
	}

	const oldPage = state.pages[ indexToAddTo ];
	const newPage = {
		...oldPage,
		elements: [
			...oldPage.elements,
			...elements,
		],
	};

	const newPages = [
		...state.pages.slice( 0, indexToAddTo ),
		newPage,
		...state.pages.slice( indexToAddTo + 1 ),
	];

	const elementIds = elements.map( ( { id } ) => id );

	const newSelection = isInsertingOnCurrentPage ? elementIds : state.selection;

	return {
		...state,
		pages: newPages,
		selection: newSelection,
	};
}

export default addElements;
