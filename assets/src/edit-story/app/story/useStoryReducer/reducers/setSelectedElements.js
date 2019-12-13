/**
 * Internal dependencies
 */
import { intersect } from './utils';

/**
 * Set selected elements to the given list of ids.
 *
 * If given list is not a list, do nothing.
 *
 * If given list matches (ignoring permutations) the current selection,
 * nothing happens.
 *
 * Duplicates will be removed from the given list of element ids.
 *
 * Current page and pages are unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Array.<string>} payload.elementIds Object with properties of new page
 * @return {Object} New state
 */
function setSelectedElements( state, { elementIds } ) {
	if ( ! Array.isArray( elementIds ) ) {
		return state;
	}

	const uniqueElementIds = [ ...new Set( elementIds ) ];

	// They can only be similar if they have the same length
	if ( state.selection.length === uniqueElementIds.length ) {
		// If intersection of the two lists has the same length as the old list,
		// nothing will change.
		// NB: this assumes selection is always without duplicates.
		const commonElements = intersect( state.selection, uniqueElementIds );
		if ( commonElements.length === state.selection.length ) {
			return state;
		}
	}

	return {
		...state,
		selection: uniqueElementIds,
	};
}

export default setSelectedElements;
