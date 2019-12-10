/**
 * WordPress dependencies
 */
import { useReducer } from '@wordpress/element';

/**
 * Internal dependencies
 */
import actions from './actions';
import reducer from './reducer';

const INITIAL_STATE = {
	pages: [],
	current: null,
	selection: [],
};

function useStoryReducer() {
	const [ state, dispatch ] = useReducer( reducer, INITIAL_STATE );

	const dispatchableActions = Object.keys( actions )
		.reduce(
			( collection, action ) => ( { ...collection, [ action ]: actions[ action ]( dispatch ) } ),
			{},
		);

	return {
		state,
		actions: dispatchableActions,
	};
}

export default useStoryReducer;
