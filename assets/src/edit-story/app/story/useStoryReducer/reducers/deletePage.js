/**
 * Delete page by id or delete current page if no id given.
 *
 * If another page than current page is deleted, it will remain current page.
 *
 * If the current page is deleted, the next page will become current.
 * If no next page, previous page will become current.
 *
 * If state only has one or zero pages, nothing happens.
 *
 * If a page is deleted, selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageId Page id to delete. If null, delete current page
 * @return {Object} New state
 */
function deletePage( state, { pageId } ) {
	if ( state.pages.length <= 1 ) {
		return state;
	}

	const idToDelete = pageId === null ? state.current : pageId;

	const pageIndex = state.pages.findIndex( ( { id } ) => id === idToDelete );

	if ( pageIndex === -1 ) {
		return state;
	}

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		...state.pages.slice( pageIndex + 1 ),
	];

	let newCurrent = state.current;

	if ( idToDelete === state.current ) {
		// Current page is at the same index unless it's off the end of the array
		const newCurrentIndex = Math.min( newPages.length - 1, pageIndex );
		newCurrent = newPages[ newCurrentIndex ].id;
	}

	return {
		...state,
		pages: newPages,
		current: newCurrent,
		selection: [],
	};
}

export default deletePage;
