/**
 * WordPress dependencies
 */
import { useReducer } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { exposedActions, internalActions } from './actions';
import reducer from './reducer';

const INITIAL_STATE = {
	pages: [],
	current: null,
	selection: [],
};

function useStoryReducer() {
	const [ state, dispatch ] = useReducer( reducer, INITIAL_STATE );

	const wrapWithDispatch = ( actions ) => Object.keys( actions )
		.reduce(
			( collection, action ) => ( { ...collection, [ action ]: actions[ action ]( dispatch ) } ),
			{},
		);

	const internal = wrapWithDispatch( internalActions, dispatch );
	const api = wrapWithDispatch( exposedActions, dispatch );

	return {
		state,
		internal,
		api,
	};
}

export default useStoryReducer;
