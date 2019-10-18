/**
 * WordPress dependencies
 */
import isShallowEqual from '@wordpress/is-shallow-equal';

/**
 * Returns the setter used for the proxied snap target objects.
 *
 * The setter is a small helper that:
 *
 * - Prevents duplicates
 * - Keeps snap targets within lower and upper bounds.
 * - Combines snap lines if there are multiple for a given target
 *
 * @param {number} lowerBound Lower limit for snap targets, i.e. the page dimensions.
 * @param {number} upperBound Upper limit for snap targets, i.e. the page dimensions.
 * @return {Function} Proxy setter.
 */
const getSetter = ( lowerBound, upperBound ) => ( obj, prop, value ) => {
	prop = Math.round( prop );

	if ( prop < lowerBound || prop > upperBound ) {
		// Discard any snap lines outside the page.
		return true;
	}

	const hasSnapLine = ( item ) =>
		obj[ prop ]
			.find( ( snapLine ) =>
				isShallowEqual( item[ 0 ], snapLine[ 0 ] ) &&
				isShallowEqual( item[ 1 ], snapLine[ 1 ] )
			);

	if ( obj.hasOwnProperty( prop ) ) {
		if ( hasSnapLine( value ) ) {
			// We already have a completely identical snap line.
			return true;
		}

		// We have at least one snap line at this position, add new to the list.
		obj[ prop ].push( value );
	} else {
		// First snap lines at this position, create new list.
		obj[ prop ] = [ value ];
	}

	// Always keep the list sorted.
	obj[ prop ] = obj[ prop ].sort();

	return true;
};

/**
 * Create snap list via proxy that contains initial snap lines for edges and center of page.
 *
 * @param {import('./types').LineCreator} getLine       Function to create lines based off maximum value.
 * @param {number}                        maxValue      Maximum value of page in this direction.
 * @param {boolean}                       includeEdges  Include edges in initial list.
 * @return {import('./types').SnapLines} An initial list of snap lines ready to be extended.
 */
const createSnapList = ( getLine, maxValue, includeEdges = true ) => new Proxy(
	// Create initial list of snap lines.
	{
		// Center of the page.
		[ maxValue / 2 ]: [ getLine( maxValue / 2 ) ],
		...( includeEdges ?
			{
				// Start page border.
				0: [ getLine( 0 ) ],
				// End page border.
				[ maxValue ]: [ getLine( maxValue ) ],
			} :
			{}
		),
	},
	{
		set: getSetter( 0, maxValue ),
	},
);

export default createSnapList;
