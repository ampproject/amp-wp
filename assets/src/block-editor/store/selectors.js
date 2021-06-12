/**
 * Returns whether the current theme has AMP support.
 *
 * @param {Object} state Editor state.
 * @return {boolean} Whether the current theme has AMP support.
 */
export function hasThemeSupport( state ) {
	return Boolean( state.hasThemeSupport );
}

/**
 * Returns whether the current user has the AMP DevTools enabled.
 *
 * @param {Object} state The editor state.
 * @return {boolean} Whether the DevTools are enabled.
 */
export function isDevToolsEnabled( state ) {
	return state.isDevToolsEnabled;
}

/**
 * Returns whether the current site is in Standard mode (AMP-first) as opposed to Transitional (paired).
 *
 * @param {Object} state Editor state.
 * @return {boolean} Whether the current site is AMP-first.
 */
export function isStandardMode( state ) {
	return Boolean( state.isStandardMode );
}

/**
 * Returns the AMP validation error messages.
 *
 * @param {Object} state The editor state.
 * @return {string[]} The validation error messages.
 */
export function getErrorMessages( state ) {
	return state.errorMessages;
}

/**
 * Returns the AMP preview link (URL).
 *
 * @param {Object} state The editor state.
 * @return {string} The AMP preview link URL.
 */
export function getAmpPreviewLink( state ) {
	return state.ampPreviewLink;
}

/**
 * Returns the AMP URL.
 *
 * @param {Object} state The editor state.
 * @return {string} The AMP URL.
 */
export function getAmpUrl( state ) {
	return state.ampUrl;
}

/**
 * Returns the list of AMP blocks found in the post.
 *
 * @param {Object} state The editor state.
 * @return {string[]} The list of AMP blocks in post.
 */
export function getAmpBlocksInUse( state ) {
	return state.ampBlocksInUse;
}
