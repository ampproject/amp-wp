/**
 * Insert page as the new last page.
 *
 * Current page will be updated to point to the newly inserted page.
 *
 * Selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Object} payload.page Object with properties of new page
 * @return {Object} New state
 */
function addPage( state, { page } ) {
	const newIndex = state.pages.length;
	return {
		...state,
		pages: [
			...state.pages,
			page,
		],
		current: newIndex,
		selection: [],
	};
}

export default addPage;
