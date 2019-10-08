/**
 * Internal dependencies
 */
import findClosestSnap from './findClosestSnap';
import findBestMatch from './findBestMatch';

/**
 * Get the best match(es) from a list of snap lines based on actual position.
 *
 * @param {import('./types').SnapLines} snapLines Object with lists of snap lines indexes by coordinate.
 * @param {number} start Value of near edge of current object
 * @param {number} end Value of far edge of current object
 * @param {number} gap Maximum gap from edge to snap line for line to be considered.
 * @return {Array.<import('./types').Line>} The lines that correspond to the single best match for any edge.
 */
const getBestSnapLines = ( snapLines, start, end, gap ) => {
	// Go through all snap targets and find the one that is closest.
	const snapTargets = Object.keys( snapLines );

	const center = ( start + end ) / 2;

	const matcher = ( value ) => findClosestSnap( value, snapTargets, gap );

	const bestMatch = findBestMatch( matcher, start, end, center );

	if ( ! bestMatch ) {
		return [];
	}

	return snapLines[ bestMatch ];
};

export default getBestSnapLines;
