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
 * Returns whether the website experience is enabled.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether website experienced enabled.
 */
export function isWebsiteEnabled( state ) {
	return Boolean( state.isWebsiteEnabled );
}

/**
 * Returns whether the stories experience is enabled.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether stories experienced enabled.
 */
export function isStoriesEnabled( state ) {
	return Boolean( state.isStoriesEnabled );
}

/**
 * Returns the default AMP status.
 *
 * @param {Object} state Editor state.
 *
 * @return {string} The default AMP status.
 */
export function getDefaultStatus( state ) {
	return state.defaultStatus;
}

/**
 * Returns the possible AMP statuses.
 *
 * @param {Object} state Editor state.
 *
 * @return {string[]} The possible AMP statuses, 'enabled' and 'disabled'.
 */
export function getPossibleStatuses( state ) {
	return state.possibleStatuses;
}
