/**
 * Internal dependencies
 */
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';

/**
 * Retrieve slugs of plugins and themes from a list of validation results.
 *
 * See the corresponding PHP logic in `\AMP_Validated_URL_Post_Type::render_sources_column()`.
 *
 * @param {Object[]} validationResults
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
			if ( source.type === 'plugin' ) {
				plugins.add( getPluginSlugFromFile( source.name ) );
			} else if ( source.type === 'theme' ) {
				themes.add( source.name );
			}
		}
	}

	// Skip including AMP in the summary, since AMP is like core.
	plugins.delete( 'amp' );

	// Skip including Gutenberg in the summary if there is another plugin, since Gutenberg is like core.
	if ( plugins.size > 1 && plugins.has( 'gutenberg' ) ) {
		plugins.delete( 'gutenberg' );
	}

	return {
		plugins: [ ...plugins ],
		themes: [ ...themes ],
	};
}
