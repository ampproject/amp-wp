/**
 * Toggle element id in selection.
 *
 * If the given id is currently selected, remove it from selection.
 *
 * Otherwise add the given id to the current selection.
 *
 * If no id is given, do nothing.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {string} payload.elementId Id to either add or remove from selection.
 * @return {Object} New state
 */
function toggleElement( state, { elementId } ) {
	if ( ! elementId ) {
		return state;
	}

	const wasSelected = state.selection.includes( elementId );
	const currentPage = state.pages.find( ( { id } ) => id === state.current );
	const isBackgroundElement = currentPage.backgroundElementId === elementId;
	const hasExistingSelection = state.selection.length > 0;

	// The bg element can't be added to non-empty selection
	if ( ! wasSelected && isBackgroundElement && hasExistingSelection ) {
		return state;
	}

	const newSelection = wasSelected ?
		state.selection.filter( ( id ) => id !== elementId ) :
		[ ...state.selection, elementId ];

	return {
		...state,
		selection: newSelection,
	};
}

export default toggleElement;
