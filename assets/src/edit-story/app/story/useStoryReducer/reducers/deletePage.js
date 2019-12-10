/**
 * Internal dependencies
 */
import { isInsideRange } from './utils';

/**
 * Delete page by index or delete current page if no index given.
 *
 * If another page than current page is deleted, it will remain current page.
 *
 * If the current page is deleted, the next page will become current.
 * If no next page, previous page will become current.
 *
 * If index is outside bounds of available pages, nothing happens.
 *
 * If state only has one or zero pages, nothing happens.
 *
 * If a page is deleted, selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Page index (0-based) to delete. If null, delete current page
 * @return {Object} New state
 */
function deletePage( state, { pageIndex } ) {
	const indexToDelete = pageIndex === null ? state.current : pageIndex;

	const isWithinBounds = isInsideRange( indexToDelete, 0, state.pages.length - 1 );
	if ( ! isWithinBounds || state.pages.length === 0 ) {
		return state;
	}

	const newPages = [
		...state.pages.slice( 0, indexToDelete ),
		...state.pages.slice( indexToDelete + 1 ),
	];

	const newMaxIndex = newPages.length - 1;

	const newIndex = Math.min(
		state.index > indexToDelete ? state.index - 1 : state.index,
		newMaxIndex,
	);

	return {
		...state,
		pages: newPages,
		current: newIndex,
		selection: [],
	};
}

export default deletePage;
