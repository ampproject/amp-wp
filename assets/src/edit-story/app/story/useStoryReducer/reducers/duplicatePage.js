/**
 * Duplicate page by index or current page if no index given.
 *
 * If index is outside bounds of available pages, nothing happens.
 *
 * Duplicated page will be inserted just after the page to duplicate.
 *
 * Current page will be updated to the newly inserted page.
 *
 * If page is duplicated, selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Page index to duplicate. If null, duplicate current page.
 * @return {Object} New state
 */
function duplicatePage( state, { pageIndex } ) {
	const indexToDuplicate = pageIndex === null ? state.index : pageIndex;

	const pageCount = state.pages.length;
	const maxIndex = pageCount - 1;
	if ( indexToDuplicate < 0 || indexToDuplicate > maxIndex ) {
		return state;
	}

	const newPages = [
		...state.pages.slice( 0, indexToDuplicate ),
		...state.pages.slice( indexToDuplicate, 1 ),
		...state.pages.slice( indexToDuplicate ),
	];

	const newIndex = indexToDuplicate + 1;

	return {
		...state,
		pages: newPages,
		current: newIndex,
		selection: [],
	};
}

export default duplicatePage;
