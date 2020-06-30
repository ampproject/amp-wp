/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * The error boundary component doesn't automatically catch errors in async functions.
 * This allows errors to be explicitly thrown.
 */
export function useError() {
	const [ error, setError ] = useState();

	const memoizedSetError = useCallback(
		( e ) => {
			setError( () => {
				throw e;
			} );
		},
		[],
	);

	return { error, setError: memoizedSetError };
}
