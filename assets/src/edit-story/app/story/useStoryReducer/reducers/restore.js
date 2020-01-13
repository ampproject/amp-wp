/**
 * Restore internal state completely from given state.
 *
 * Some validation is performed:
 *
 * - `pages` must be an array (if not, nothing happens).
 * - `current` must point to a legal page, if at least one page exists.
 * - `selection` is a unique array.
 * - `story` is an object.
 *
 * @param {Object} state Current state
 * @param {Object} payload New state to set.
 * @return {Object} New state
 */
function restore( state, { pages, current, selection, story, capabilities } ) {
	if ( ! Array.isArray( pages ) || pages.length === 0 ) {
		return state;
	}

	const newStory = typeof story === 'object' ? story : {};
	const newCapabilities = typeof story === 'object' ? capabilities : {};
	const newCurrent = pages.some( ( { id } ) => id === current ) ? current : pages[ 0 ].id;
	const newSelection = Array.isArray( selection ) ? [ ...new Set( selection ) ] : [];

	return {
		pages,
		current: newCurrent,
		selection: newSelection,
		story: newStory,
		capabilities: newCapabilities,
	};
}

export default restore;
