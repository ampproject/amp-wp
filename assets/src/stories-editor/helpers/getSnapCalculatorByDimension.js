/**
 * Internal dependencies
 */
import createSnapList from './createSnapList';

/**
 * Higher-higher order function that in the end creates a list of snap targets.
 *
 * First it is invoked with the "direction" of the snaps to calculate, creating the two
 * functions below, `getHorizontalSnaps` and `getVerticalSnaps`.
 *
 * These functions in turn take a list of sibling element positions used to create the
 * snap calculator function.
 *
 * This final snap calculator function takes the current coordinates of the dragged
 * element (in the relevant dimension) and returns a list of all available snap lines
 * relative to the snap target.
 *
 * @param {Function} getLine Function to create lines based.
 * @param {number} maxValue Maximum value of page in this direction.
 * @param {Array.<number>} primary Coordinate property names in primary direction.
 * @param {Array.<number>} secondary Coordinate property names in secondary direction.
 * @return {Function} Function mapping an actual object's position to all possible snap targets.
 */
const getSnapCalculatorByDimension = (
	getLine,
	maxValue,
	[ startProp, endProp ],
	[ minProp, maxProp ],
) => ( siblingPositions ) => ( targetMin, targetMax ) => {
	const snaps = createSnapList( getLine, maxValue );

	for ( const coords of siblingPositions ) {
		const start = coords[ startProp ];
		const end = coords[ endProp ];
		const center = ( start + end ) / 2;

		const min = Math.min( targetMin, coords[ minProp ] );
		const max = Math.max( targetMax, coords[ maxProp ] );

		snaps[ start ] = getLine( start, min, max );
		snaps[ end ] = getLine( end, min, max );
		snaps[ center ] = getLine( center, min, max );
	}

	return snaps;
};

export default getSnapCalculatorByDimension;
