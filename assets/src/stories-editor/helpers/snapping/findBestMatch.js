/**
 * Get the best match for a list of values each creating a match
 *
 * @param {Array.<Object>} targets List of values and matchers to find a best match between.
 * @return {number} The single best matching value or undefined.
 */
const findBestMatch = ( ...targets ) => targets
	.map( ( { value, matcher, ...rest } ) => {
		const best = matcher( value );
		if ( ! best ) {
			return null;
		}
		return { value: best, distance: Math.abs( best - value ), ...rest };
	} )
	.filter( Boolean )
	.reduce(
		( best, candidate ) => candidate.distance < best.distance ? candidate : best,
		{ distance: Number.MAX_VALUE, value: null },
	);

export default findBestMatch;
