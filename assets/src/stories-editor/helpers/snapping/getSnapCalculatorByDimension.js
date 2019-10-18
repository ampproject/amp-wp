/**
 * Internal dependencies
 */
import createSnapList from './createSnapList';

/**
 * Higher-higher order function that in the end creates a list of snap targets.
 *
 * First it is invoked with the "direction" of the snaps to calculate, creating the two
 * functions in separate helpers, `getHorizontalSnaps` and `getVerticalSnaps`.
 *
 * These functions in turn take a list of sibling element positions used to create the
 * snap calculator function.
 *
 * This final snap calculator function takes the current coordinates of the dragged
 * element (in the relevant dimension) and returns a list of all available snap lines
 * relative to the snap target.
 *
 * @param {import('./types').LineCreator} getLine Function to create lines based.
 * @param {number} maxValue Maximum value of page in this direction.
 * @param {Array.<string>} primary Coordinate property names in primary direction.
 * @param {Array.<string>} secondary Coordinate property names in secondary direction.
 * @return {Array.<import('./types').SnapTargetsEnhancer>} Function mapping an actual object's position to all possible snap targets.
 */
const getSnapCalculatorByDimension = (
	getLine,
	maxValue,
	[ startProp, endProp ],
	[ minProp, maxProp ],
) => ( siblingPositions ) => ( targetMin, targetMax ) => {
	const edgeSnaps = createSnapList( getLine, maxValue );
	const centerSnaps = createSnapList( getLine, maxValue, false );

	for ( const coords of siblingPositions ) {
		const start = coords[ startProp ];
		const end = coords[ endProp ];
		const center = ( start + end ) / 2;

		const min = Math.min( targetMin, coords[ minProp ] );
		const max = Math.max( targetMax, coords[ maxProp ] );

		edgeSnaps[ start ] = getLine( start, min, max );
		edgeSnaps[ end ] = getLine( end, min, max );
		centerSnaps[ center ] = getLine( center, min, max );
	}

	return [ edgeSnaps, centerSnaps ];
};

export default getSnapCalculatorByDimension;
