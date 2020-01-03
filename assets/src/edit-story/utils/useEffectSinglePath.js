/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';

function useEffectSinglePath( callback, deps = undefined ) {
	const scheduledRef = useRef( null );
	const lastRef = useRef( null );

	/* eslint-disable react-hooks/exhaustive-deps */
	useEffect( () => {
		const wasScheduled = Boolean( scheduledRef.current );
		// Always use the latest callback scheduled.
		scheduledRef.current = callback;

		if ( wasScheduled ) {
			return;
		}

		const promise = Promise.resolve( lastRef.current )
			.then( () => {
				const { current } = scheduledRef;
				scheduledRef.current = null;
				return current();
			} )
			.catch( ( reason ) => {
				// Rethrow asynchronously and ignore the error to avoid blocking
				// the queue.
				setTimeout( () => {
					throw reason;
				} );
			} );
		lastRef.current = promise;
	}, deps );
/* eslint-enable react-hooks/exhaustive-deps */
}

export default useEffectSinglePath;
