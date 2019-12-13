/**
 * External dependencies
 */
import { renderHook, act } from '@testing-library/react-hooks';

/**
 * Internal dependencies
 */
import useStoryReducer from '../useStoryReducer';

export function setupReducer() {
	const { result } = renderHook( () => useStoryReducer() );

	// convert each method to be wrapped in act and return the new state
	const wrapWithAct = ( methods ) => Object.keys( methods )
		.reduce(
			( obj, methodName ) => {
				const method = methods[ methodName ];
				const wrapped = ( parms ) => {
					act( () => method( parms ) );
					return result.current.state;
				};
				return {
					...obj,
					[ methodName ]: wrapped,
				};
			},
			{},
		);

	return wrapWithAct( {
		...result.current.api,
		...result.current.internal,
	} );
}
