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

	return {
		...state,
		selection: [ ...state.selection, elementId ],
	};
}

export default selectElement;
