/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * The error boundary component doesn't automatically catch errors in async functions.
 * This allows errors to be explicitly thrown.
 */
export function useAsyncError() {
	const [ error, setAsyncError ] = useState();

	const memoizedSetError = useCallback(
		( e ) => {
			setAsyncError( () => {
				throw e;
			} );
		},
		[],
	);

	return { error, setAsyncError: memoizedSetError };
}
