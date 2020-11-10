/**
 * Returns the object set on the back end via an inline script.
 *
 * @param {string} varName The name of the JS variable set
 * @param {any} defaultValue A default value to return if variable is not found.
 */
export function useInlineData( varName, defaultValue = {} ) {
	return global[ varName ] || defaultValue;
}
