/**
 * External dependencies
 */
import ResizeObserver from 'resize-observer-polyfill';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * @param {!{current: ?Element}} ref Target node ref.
 * @param {function( {width: number, height: number} )} handler The resize
 * handler.
 * @param {!Array=} deps The effect's dependencies.
 */
function useResizeEffect( ref, handler, deps = undefined ) {
	useEffect(
		() => {
			const node = ref.current;
			if ( ! node ) {
				return null;
			}

			const observer = new ResizeObserver( ( entries ) => {
				const last = entries.length > 0 ? entries[ entries.length - 1 ] : null;
				if ( last ) {
					const { width, height } = last.contentRect;
					handler( { width, height } );
				}
			} );

			observer.observe( node );

			return () => observer.disconnect();
		},
		// eslint-disable-next-line react-hooks/exhaustive-deps
		deps || [],
	);
}

export default useResizeEffect;
