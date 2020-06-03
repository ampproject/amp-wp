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
 * Returns whether the current site is in Standard mode (AMP-first) as opposed to Transitional (paired).
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether the current site is AMP-first.
 */
export function isStandardMode( state ) {
	return Boolean( state.isStandardMode );
}

/**
 * Returns the AMP validation error messages.
 *
 * @param {Object} state The editor state.
 *
 * @return {string[]} The validation error messages.
 */
export function getErrorMessages( state ) {
	return state.errorMessages;
}

/**
 * Returns the AMP slug used in the query var, like 'amp'.
 *
 * @param {Object} state The editor state.
 *
 * @return {string} The slug for AMP, like 'amp'.
 */
export function getAmpSlug( state ) {
	return state.ampSlug;
}
