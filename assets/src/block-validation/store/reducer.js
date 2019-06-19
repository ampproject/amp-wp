/**
 * Reducer handling changes related to block validation.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
export default ( state = undefined, action ) => {
	const { type, url, error, clientId } = action;

	switch ( type ) {
		case 'ADD_VALIDATION_ERROR':
			const errors = state ? state.errors : [];
			const enhancedError = {
				...error,
				clientId,
			};

			return {
				...state,
				errors: [ ...errors, enhancedError ],
			};
		case 'RESET_VALIDATION_ERRORS':
			return {
				...state,
				errors: [],
			};

		case 'UPDATE_REVIEW_LINK':
			return {
				...state,
				reviewLink: url,
			};
	}

	return state;
};
