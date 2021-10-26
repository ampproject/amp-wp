/**
 * Retrieve plugin slug out of the source path.
 *
 * @param {string} path Plugin source path.
 * @return {string} Plugin slug.
 */
export function getPluginSlugFromPath( path = '' ) {
	return path?.match( /^(?:[^\/]*\/)*([^.]*)/ )?.[ 1 ] || '';
}
