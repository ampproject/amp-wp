/**
 * Returns general validation erroxrs.
 *
 * @param {Object} state Editor state.
 *
 * @return {Array} Validation errors.
 */
export function getValidationErrors( state ) {
	return state.errors;
}

/**
 * Returns the block validation errors for a given clientId.
 *
 * @param {Object} state    Editor state.
 * @param {string} clientId Block client ID.
 *
 * @return {Array} Block validation errors.
 */
export function getBlockValidationErrors( state, clientId ) {
	return state.errors.filter( ( error ) => error.clientId === clientId );
}

/**
 * Returns the URL for reviewing validation issues.
 *
 * @param {Object} state Editor state.
 *
 * @return {string} Validation errors review link.
 */
export function getReviewLink( state ) {
	return state.reviewLink;
}

/**
 * Returns whether sanitization errors are auto-accepted.
 *
 * Auto-acceptance is from either checking 'Automatically accept sanitization...' or from being in Standard mode.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether sanitization errors are auto-accepted.
 */
export function isSanitizationAutoAccepted( state ) {
	return Boolean( state.isSanitizationAutoAccepted );
}
