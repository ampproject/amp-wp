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
		const { error } = result;

		if ( ! error?.sources ) {
			continue;
		}

		for ( const source of error.sources ) {
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
