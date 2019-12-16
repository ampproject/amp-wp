
/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';
/**
 * It's a bit weird to directly set a state to be a function (as setFoo calls
 * any function given to unwrap the inner value, which can then be a function),
 * so we use a wrapper object in stead of double-functioning.
 *
 * @param {Function} initialValue  Initial value of the variable
 * @return {Array} Array of value, setter and clearer.
 */
function useFunctionState( initialValue = undefined ) {
	const [ value, setValue ] = useState( { handler: initialValue } );

	const setter = useCallback(
		( handler ) => setValue( { handler: typeof handler === 'function' ? handler : undefined } ),
		[ setValue ],
	);

	const clearer = useCallback(
		() => setValue( { handler: undefined } ),
		[ setValue ],
	);

	return [ value.handler, setter, clearer ];
}

export default useFunctionState;
