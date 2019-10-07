/**
 * Get the best match for a list of values each creating a match
 *
 * @param {Function.<number>} matcher Function finding closest match for a single value or null
 * @param {Array.<number>} values List of values to find a best match between.
 * @return {number} The single best matching value or undefined.
 */
const findBestMatch = ( matcher, ...values ) => values
	.map( ( value ) => {
		const best = matcher( value );
		if ( ! best ) {
			return null;
		}
		return { value: best, distance: Math.abs( best - value ) };
	} )
	.filter( Boolean )
	.reduce(
		( best, candidate ) => candidate.distance < best.distance ? candidate : best,
		{ distance: Number.MAX_VALUE, value: null },
	)
	.value;

export default findBestMatch;
