/**
 * Get plugin slug from file path.
 *
 * If the plugin file is in a directory, then the slug is just the directory name. Otherwise, if the file is not
 * inside of a directory and is just a single-file plugin, then the slug is the filename of the PHP file.
 *
 * If the file path contains a file extension, it will be stripped as well.
 *
 * See the corresponding PHP logic in `\AmpProject\AmpWP\get_plugin_slug_from_file()`.
 *
 * @param {string} path Plugin file path.
 * @return {string} Plugin slug.
 */
export function getPluginSlugFromFile( path = '' ) {
	return path.replace( /\/.*$/, '' ).replace( /\.php$/, '' );
}
