/**
 * Duplicate the given list of elements.
 *
 * TODO: Describe and implement if we even need this. Do we?
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Array.<string>} payload.elementIds List of element ids to duplicate.
 * @return {Object} New state
 */
function duplicateElements( state, { elementIds } ) {
	if ( elementIds.length === 0 ) {
		return state;
	}

	return state;
}

export default duplicateElements;
