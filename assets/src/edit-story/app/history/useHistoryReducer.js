
/**
 * WordPress dependencies
 */
import { useReducer, useCallback } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

const ADD_ENTRY = 'add';
const CLEAR_HISTORY = 'clear';
const REPLAY = 'replay';

const EMPTY_STATE = { entries: [], offset: 0, replayState: null };

const reducer = ( size ) => ( state, { type, payload } ) => {
	switch ( type ) {
		case ADD_ENTRY:
			// First check if everything in payload matches the current `replayState`,
			// if so, update `offset` to match the state in entries and clear `replayState`
			// and of course leave entries unchanged.
			if ( state.replayState ) {
				const isReplay = Object.keys( state.replayState )
					.every( ( key ) => state.replayState[ key ] === payload[ key ] );

				if ( isReplay ) {
					return {
						...state,
						offset: state.entries.indexOf( state.replayState ),
						replayState: null,
					};
				}
			}

			// If not, trim `entries` from `offset` (basically destroy all undone states),
			// add new entry but limit entire storage to `size`
			// and clear `offset` and `replayState`.
			return {
				entries: [
					payload,
					...state.entries.slice( state.offset ),
				].slice( 0, size ),
				offset: 0,
				replayState: null,
			};

		case REPLAY:
			return {
				...state,
				replayState: state.entries[ payload ],
			};

		case CLEAR_HISTORY:
			return {
				...EMPTY_STATE,
			};

		default:
			const text = sprintf(
				/* translators: %s: Type of error. */
				__( 'Unknown history reducer action: %s', 'amp' ),
				type,
			);
			throw new Error( text );
	}
};

function useHistoryReducer( size ) {
	// State has 3 parts:
	//
	// `state.entries` is an array of the last changes (up to `size`) to
	// the object with the most recent at position 0.
	//
	// `state.offset` is a pointer to the currently active entry. This will
	// almost always be 0 unless the user recently did an undo without making
	// any new changes since.
	//
	// `state.replayState` is the state that the user most recently tried to
	// undo/redo to - it will be null except for the very short timespan
	// between the user pressing undo and the app updating to that desired
	// state.
	const [ state, dispatch ] = useReducer( reducer( size ), { ...EMPTY_STATE } );

	const { entries, offset, replayState } = state;
	const historyLength = entries.length;

	const replay = useCallback( ( deltaOffset ) => {
		const newOffset = offset + deltaOffset;
		if ( newOffset < 0 || newOffset >= historyLength - 1 ) {
			return false;
		}

		dispatch( { type: REPLAY, payload: newOffset } );
		return true;
	}, [ dispatch, offset, historyLength ] );

	const undo = useCallback( ( count = 1 ) => {
		return replay( typeof count === 'number' ? count : 1 );
	}, [ replay ] );

	const redo = useCallback( ( count = 1 ) => {
		return replay( typeof count === 'number' ? -count : -1 );
	}, [ replay ] );

	const clearHistory = useCallback( () => {
		return dispatch( { type: CLEAR_HISTORY } );
	}, [ dispatch ] );

	const appendToHistory = useCallback( ( entry ) => {
		dispatch( { type: ADD_ENTRY, payload: entry } );
	}, [ dispatch ] );

	return {
		replayState,
		appendToHistory,
		clearHistory,
		offset,
		historyLength,
		undo,
		redo,
	};
}

export default useHistoryReducer;
