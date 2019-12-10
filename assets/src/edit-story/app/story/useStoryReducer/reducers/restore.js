/**
 * Restore internal state completely from given state.
 *
 * No validation is performed besides the names of the state properties.
 *
 * @param {Object} state Current state
 * @param {Object} payload New state to set.
 * @return {Object} New state
 */
function restore( state, { pages, current, selection } ) {
	return { pages, current, selection };
}

export default restore;
