/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

import useLoadStory from './effects/useLoadStory';
import useCurrentPage from './effects/useCurrentPage';
import useHistoryEntry from './effects/useHistoryEntry';
import useHistoryReplay from './effects/useHistoryReplay';

import useAddBlankPage from './actions/useAddBlankPage';
import useClearSelection from './actions/useClearSelection';

function StoryProvider( { storyId, children } ) {
	// Story state is stored in these three immutable variables only!
	// Don't update 1 of these in an effect based off another base variable.
	// Only update these directly as a response to user or api interactions.
	const [ pages, setPages ] = useState( [] );
	const [ currentPageId, setCurrentPageById ] = useState( null );
	const [ selectedElementIds, setSelectedElementIds ] = useState( [] );

	// These states are all derived from the above three variables and help keep the api easier.
	// These will update based off the above in effects but should never be directly manipulated outside this component.
	const [ currentPageNumber, setCurrentPageNumber ] = useState( null );
	const [ currentPage, setCurrentPage ] = useState( null );

	const clearSelection = useClearSelection( { selectedElementIds, setSelectedElementIds } );
	const addBlankPage = useAddBlankPage( { pages, setPages, clearSelection } );

	useLoadStory( { storyId, setPages, setCurrentPageById, clearSelection } );
	useCurrentPage( { currentPageId, pages, setCurrentPage, setCurrentPageNumber } );
	useHistoryEntry( { currentPageId, pages, selectedElementIds } );
	useHistoryReplay( { setCurrentPageById, setPages, setSelectedElementIds } );

	const state = {
		state: {
			pages,
			currentPageId,
			currentPageNumber,
			currentPage,
			selectedElementIds,
		},
		actions: {
			setCurrentPageById,
			addBlankPage,
			clearSelection,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

StoryProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	storyId: PropTypes.number,
};

export default StoryProvider;
