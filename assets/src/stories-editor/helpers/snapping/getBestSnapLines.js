/**
 * Internal dependencies
 */
import findClosestSnap from './findClosestSnap';
import findBestMatch from './findBestMatch';

/**
 * Get the best match(es) from a list of snap lines based on actual position.
 *
 * @param {import('./types').SnapLines} edgeLines Object with lists of snap lines indexes by coordinate for edges of object.
 * @param {import('./types').SnapLines} centerLines Object with lists of snap lines indexes by coordinate for center of object.
 * @param {number} start Value of near edge of current object
 * @param {number} end Value of far edge of current object
 * @param {number} gap Maximum gap from edge to snap line for line to be considered.
 * @return {Array.<import('./types').Line>} The lines that correspond to the single best match for any edge.
 */
const getBestSnapLines = ( edgeLines, centerLines, start, end, gap ) => {
	// Go through all snap targets and find the one that is closest.
	const edgeTargets = Object.keys( edgeLines );
	const centerTargets = Object.keys( centerLines );

	const center = ( start + end ) / 2;

	const matcher = ( targets ) => ( value ) => findClosestSnap( value, targets, gap );

	const { value, isEdgeTarget } = findBestMatch(
		{ value: start, matcher: matcher( edgeTargets ), isEdgeTarget: true },
		{ value: end, matcher: matcher( edgeTargets ), isEdgeTarget: true },
		{ value: center, matcher: matcher( centerTargets ), isEdgeTarget: false },
	);

	if ( ! value ) {
		return [];
	}

	return isEdgeTarget ? edgeLines[ value ] : centerLines[ value ];
};

export default getBestSnapLines;
