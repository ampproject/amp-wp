/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Synchronize multiple refs to a single ref
 *
 * This is used when receiving a forwarded ref, but also needing an internal one.
 *
 * @param {Array} refs  List of refs to synchronize
 * @return {Function} A callback to be used as `ref` for element.
 */
function useCombinedRefs( ...refs ) {
	const setRef = useCallback( ( node ) => {
		refs.forEach( ( ref ) => {
			if ( ! ref ) {
				// Ignore non-existing refs
				return;
			}

			// Set ref value correctly
			if ( typeof ref === 'function' ) {
				ref( node );
			} else {
				ref.current = node;
			}
		} );
	}, [ refs ] );

	return setRef;
}

export default useCombinedRefs;
