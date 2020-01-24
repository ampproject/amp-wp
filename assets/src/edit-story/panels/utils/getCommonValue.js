/**
 * Get the common value `property` for all objects in `list`, if they
 * in fact are all the same. If they are not all equal, return an empty string.
 *
 * Example usage:
 * ```
 * getCommonValue( [ { a: 1 }, { a:  1  } ], 'a' );  // returns: 1
 * getCommonValue( [ { a: 1 }, {        } ], 'a' );  // returns: ''
 * getCommonValue( [ { a: 1 }, { a:  2  } ], 'a' );  // returns: ''
 * getCommonValue( [ { a: 1 }, { a: '1' } ], 'a' );  // returns: ''
 * ```
 *
 * @param {Array.<Object>} list  List of objects
 * @param {string} property Property to check on all objects
 * @return {any} Returns common value or empty string if not similar
 */
function getCommonValue( list, property ) {
	const first = list[ 0 ][ property ];
	const allMatch = list.every( ( el ) => el[ property ] === first );
	return allMatch ? first : '';
}

export default getCommonValue;
