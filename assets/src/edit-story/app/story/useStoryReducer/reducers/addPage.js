/**
 * Internal dependencies
 */
import { isInsideRange } from './utils';

/**
 * Insert page at the given position.
 *
 * If position is outside bounds or no position given, new page will be inserted after current page.
 *
 * Current page will be updated to point to the newly inserted page.
 *
 * Selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Object} payload.properties Object with properties of new page
 * @param {Object} payload.position Position at which to insert the new page. If null, insert after current
 * @return {Object} New state
 */
function addPage( state, { page, position } ) {
	const isWithinBounds = position !== null && isInsideRange( position, 0, state.pages.length - 1 );
	const currentPageIndex = state.pages.findIndex( ( { id } ) => id === state.current );
	const insertionPoint = isWithinBounds ? position : currentPageIndex + 1;

	const { id } = page;

	// Ensure new page has elements array and background id
	const newPage = {
		elements: [],
		backgroundElementId: null,
		...page,
	};

	return {
		...state,
		pages: [
			...state.pages.slice( 0, insertionPoint ),
			newPage,
			...state.pages.slice( insertionPoint ),
		],
		current: id,
		selection: [],
	};
}

export default addPage;
