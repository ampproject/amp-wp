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

	const newSelection = state.selection.includes( elementId ) ?
		state.selection.filter( ( id ) => id !== elementId ) :
		[ ...state.selection, elementId ];

	return {
		...state,
		selection: newSelection,
	};
}

export default toggleElement;
