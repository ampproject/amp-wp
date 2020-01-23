/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCanvas from './useCanvas';

/**
 * @param {string} id Target element's id.
 * @param {function(?Object)} handler The transform handler. The argument is
 * the frame object. The `null` value resets the transform.
 * @param {!Array=} deps The effect's dependencies.
 */
function useTransformHandler( id, handler, deps = undefined ) {
	const {
		actions: { registerTransformHandler },
	} = useCanvas();

	useEffect(
		() => registerTransformHandler( id, handler ),
		// eslint-disable-next-line react-hooks/exhaustive-deps
		deps,
	);
}

export default useTransformHandler;
