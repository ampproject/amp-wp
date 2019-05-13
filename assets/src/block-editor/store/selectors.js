/**
 * Returns the block validation errors for a given clientId.
 *
 * @param {Object} state    Editor state.
 * @param {string} clientId Block client ID.
 *
 * @return {Array} Block validation errors.
 */
export function getBlockValidationErrors( state, clientId ) {
	return state.errorsByClientId[ clientId ] || [];
}

/**
 * Returns whether the current theme has AMP support.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether the current theme has AMP support.
 */
export function hasThemeSupport( state ) {
	return Boolean( state.hasThemeSupport );
}

/**
 * Returns whether the current site uses native AMP.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether the current site uses native AMP.
 */
export function isNativeAMP( state ) {
	return Boolean( state.isNativeAMP );
}

export function getDefaultStatus( state ) {
	return state.defaultStatus;
}

export function getPossibleStatuses( state ) {
	return state.possibleStatuses;
}
