/**
 * Retrieve slugs of plugins and themes from a list of validation results.
 *
 * See the corresponding PHP logic in `\AMP_Validated_URL_Post_Type::render_sources_column()`.
 *
 * @param {Array} validationResults
 * @return {Object} An object consisting of `pluginSlugs` and `themeSlugs` arrays.
 */
export function getSlugsFromValidationResults( validationResults = [] ) {
	const plugins = new Set();
	const themes = new Set();

	for ( const result of validationResults ) {
		if ( ! result.sources ) {
			continue;
		}

		for ( const source of result.sources ) {
			// Skip including Gutenberg in the summary if there is another plugin, since Gutenberg is like core.
			if ( result.sources.length > 1 && source.type === 'plugin' && source.name === 'gutenberg' ) {
				continue;
			}

			if ( source.type === 'plugin' && source.name !== 'amp' ) {
				plugins.add( source.name.match( /(.*?)(?:\.php)?$/ )[ 1 ] );
			} else if ( source.type === 'theme' ) {
				themes.add( source.name );
			}
		}
	}

	return {
		plugins: [ ...plugins ],
		themes: [ ...themes ],
	};
}
