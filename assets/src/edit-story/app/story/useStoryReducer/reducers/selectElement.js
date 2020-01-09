/**
 * Add the given id to the current selection.
 *
 * If no id is given or id is already in the current selection, nothing happens.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {string} payload.elementId Element id to add to the current selection.
 * @return {Object} New state
 */
function selectElement( state, { elementId } ) {
	if ( ! elementId || state.selection.includes( elementId ) ) {
		return state;
	}

	const currentPage = state.pages.find( ( { id } ) => id === state.current );
	const isBackgroundElement = currentPage.backgroundElementId === elementId;
	const hasExistingSelection = state.selection.length > 0;
	// The bg element can't be added to non-empty selection
	if ( isBackgroundElement && hasExistingSelection ) {
		return state;
	}

	return {
		...state,
		selection: [ ...state.selection, elementId ],
	};
}

export default selectElement;
