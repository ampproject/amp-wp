/**
 * External dependencies
 */
import memize from 'memize';

/**
 * Given a rotation angle, finds the closest angle to snap to.
 *
 * Inspired by the implementation in re-resizable.
 *
 * @see https://github.com/bokuweb/re-resizable
 *
 * @param {number} number
 * @param {Array|Function<number>} snap List of snap targets or function that provider
 * @param {number} snapGap Minimum gap required in order to move to the next snapping target
 * @return {number} New angle.
 */
const findClosestSnap = memize( ( number, snap, snapGap ) => {
	const snapArray = typeof snap === 'function' ? snap( number ) : snap;

	const closestGapIndex = snapArray.reduce(
		( prev, curr, index ) => ( Math.abs( curr - number ) < Math.abs( snapArray[ prev ] - number ) ? index : prev ),
		0,
	);
	const gap = Math.abs( snapArray[ closestGapIndex ] - number );

	return snapGap === 0 || gap < snapGap ? snapArray[ closestGapIndex ] : number;
} );

export default findClosestSnap;
