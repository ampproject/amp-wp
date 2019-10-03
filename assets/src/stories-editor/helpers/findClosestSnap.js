/**
 * External dependencies
 */
import memize from 'memize';

/**
 * Given a number, finds the closest snap target.
 *
 * @see https://github.com/bokuweb/re-resizable
 *
 * @param {number} number Given number.
 * @param {Array|Function<number>} snap List of snap targets or function that provides them.
 * @param {number} snapGap Minimum gap required in order to move to the next snapping target
 * @return {?number} Snap target if found.
 */
const findClosestSnap = memize( ( number, snap, snapGap ) => {
	const snapArray = typeof snap === 'function' ? snap( number ) : snap;

	const closestGapIndex = snapArray.reduce(
		( prev, curr, index ) => ( Math.abs( curr - number ) < Math.abs( snapArray[ prev ] - number ) ? index : prev ),
		0,
	);
	const gap = Math.abs( snapArray[ closestGapIndex ] - number );

	return snapGap === 0 || gap < snapGap ? snapArray[ closestGapIndex ] : null;
} );

export default findClosestSnap;
