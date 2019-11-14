/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import useHistoryReducer from './useHistoryReducer';
import Context from './context';

function HistoryProvider( { children, size } ) {
	const {
		replayState,
		appendToHistory,
		clearHistory,
		offset,
		historyLength,
		undo,
		redo,
	} = useHistoryReducer( size );

	const state = {
		state: {
			replayState,
			canUndo: offset < historyLength - 1,
			canRedo: offset > 0,
		},
		actions: {
			appendToHistory,
			clearHistory,
			undo,
			redo,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

HistoryProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	size: PropTypes.number,
};

HistoryProvider.defaultProps = {
	size: 50,
};

export default HistoryProvider;
