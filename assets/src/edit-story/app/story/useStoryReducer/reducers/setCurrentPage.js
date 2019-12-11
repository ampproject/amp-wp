/**
 * Set current page to the given id.
 *
 * If id doesn't match an existing page, nothing happens.
 *
 * If page is changed, selection is cleared
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageId Page id to set as current page
 * @return {Object} New state
 */
function setCurrentPage( state, { pageId } ) {
	if ( ! state.pages.some( ( { id } ) => id === pageId ) ) {
		return state;
	}

	return {
		...state,
		current: pageId,
		selection: [],
	};
}

export default setCurrentPage;
