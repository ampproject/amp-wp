/**
 * Update story properties.
 *
 * No validation is performed and existing values are overwritten.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.properties Object with story properties to set.
 * @return {Object} New state
 */
function updateStory( state, { properties } ) {
	return {
		...state,
		story: {
			...state.story,
			...properties,
		},
	};
}

export default updateStory;
