/**
 * Remove the given id from the current selection.
 *
 * If no id is given or id is not in the current selection, nothing happens.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {string} payload.elementId Element id to remove from the current selection.
 * @return {Object} New state
 */
function unselectElement( state, { elementId } ) {
	if ( ! elementId || ! state.selection.includes( elementId ) ) {
		return state;
	}

	return {
		...state,
		selection: state.selection.filter( ( id ) => id !== elementId ),
	};
}

export default unselectElement;
