/**
 * Retrieve plugin and theme issues from the validation results.
 *
 * @param {Array} validationResults
 * @return {Object} An object consisting of arrays with plugin and theme issues.
 */
export function getSiteIssues( validationResults = [] ) {
	const pluginIssues = new Set();
	const themeIssues = new Set();

	for ( const result of validationResults ) {
		if ( ! result.sources ) {
			continue;
		}

		for ( const source of result.sources ) {
			if ( source.type === 'plugin' && source.name !== 'amp' ) {
				pluginIssues.add( source.name.match( /(.*?)(?:\.php)?$/ )[ 1 ] );
			} else if ( source.type === 'theme' ) {
				themeIssues.add( source.name );
			}
		}
	}

	return {
		pluginIssues: [ ...pluginIssues ],
		themeIssues: [ ...themeIssues ],
	};
}
