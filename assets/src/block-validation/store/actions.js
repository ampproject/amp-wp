/**
 * Returns an action object in signalling that a validation error should be added.
 *
 * @param {Object}  error    Validation error.
 * @param {?string} clientId Optional. Block client ID. Used when the validation error is specific to a block.
 *
 * @return {Object} Action object.
 */
export function addValidationError( error, clientId ) {
	return {
		type: 'ADD_VALIDATION_ERROR',
		error,
		clientId,
	};
}

/**
 * Returns an action object in signalling that validation errors should be reset.
 *
 * @return {Object} Action object.
 */
export function resetValidationErrors() {
	return {
		type: 'RESET_VALIDATION_ERRORS',
	};
}

/**
 * Returns an action object in signalling that the review URL should be updated.
 *
 * @param {string} url Issue review URL.
 *
 * @return {Object} Action object.
 */
export function updateReviewLink( url ) {
	return {
		type: 'UPDATE_REVIEW_LINK',
		url,
	};
}
