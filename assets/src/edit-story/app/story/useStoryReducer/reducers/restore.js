/**
 * Restore internal state completely from given state.
 *
 *
 * Some validation is performed:
 *
 * - `pages` must be an array (if not, nothing happens).
 * - `current` must point to a legal page, if at least one page exists.
 * - `selection` is a unique array.
 *
 * @param {Object} state Current state
 * @param {Object} payload New state to set.
 * @return {Object} New state
 */
function restore( state, { pages, current, selection } ) {
	if ( ! Array.isArray( pages ) ) {
		return state;
	}

	let newCurrent = null;
	let newSelection = [];

	if ( pages.length > 0 ) {
		newCurrent = pages.some( ( { id } ) => id === current ) ? current : pages[ 0 ].id;

		if ( Array.isArray( selection ) ) {
			newSelection = [ ...new Set( selection ) ];
		}
	}

	return {
		pages,
		current: newCurrent,
		selection: newSelection,
	};
}

export default restore;
