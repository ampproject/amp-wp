/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

function useDelayEffect( callback, deps = undefined ) {
	/* eslint-disable react-hooks/exhaustive-deps */
	useEffect(
		() => {
			const callbackPromise = new Promise( ( resolve ) => {
				setTimeout( () => {
					resolve( callback() );
				}, 0 );
			} );
			return () => {
				callbackPromise.then( ( unsubscribe ) => {
					if ( unsubscribe ) {
						unsubscribe();
					}
				} );
			};
		},
		deps,
	);
	/* eslint-enable react-hooks/exhaustive-deps */
}

export default useDelayEffect;
