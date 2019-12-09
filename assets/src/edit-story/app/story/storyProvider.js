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
import useSelectedElements from './effects/useSelectedElements';

import useAddBlankPage from './actions/useAddBlankPage';
import useClearSelection from './actions/useClearSelection';
import useDeleteSelectedElements from './actions/useDeleteSelectedElements';
import useDeleteCurrentPage from './actions/useDeleteCurrentPage';
import useToggleElementIdInSelection from './actions/useToggleElementIdInSelection';
import useSelectElementById from './actions/useSelectElementById';
import useAppendElementToCurrentPage from './actions/useAppendElementToCurrentPage';
import useSetCurrentPageByIndex from './actions/useSetCurrentPageByIndex';
import useDeletePageByIndex from './actions/useDeletePageByIndex';
import useSetPropertiesOnSelectedElements from './actions/useSetPropertiesOnSelectedElements';

function StoryProvider( { storyId, children } ) {
	// Story state is stored in these three immutable variables only!
	// Don't update 1 of these in an effect based off another base variable.
	// Only update these directly as a response to user or api interactions.
	const [ pages, setPages ] = useState( [] );
	const [ currentPageIndex, setCurrentPageIndex ] = useState( null );
	const [ selectedElementIds, setSelectedElementIds ] = useState( [] );

	const hasSelection = Boolean( selectedElementIds.length );
	const currentPage = pages[ currentPageIndex ] || null;
	const currentPageNumber = ! currentPage ? null : currentPageIndex + 1;
	const selectedElements = ! currentPage ? [] : currentPage.elements.filter( ( { id } ) => selectedElementIds.includes( id ) );

	const clearSelection = useClearSelection( { selectedElementIds, setSelectedElementIds } );
	const deleteSelectedElements = useDeleteSelectedElements( { currentPageIndex, pages, selectedElementIds, setPages, setSelectedElementIds } );
	const setCurrentPageByIndex = useSetCurrentPageByIndex( { clearSelection, setCurrentPageIndex } );
	const deletePageByIndex = useDeletePageByIndex( { clearSelection, setPages, setCurrentPageIndex } );
	const addBlankPage = useAddBlankPage( { pages, setPages, clearSelection } );
	const deleteCurrentPage = useDeleteCurrentPage( { currentPage, pages, setPages, addBlankPage, setCurrentPageIndex, currentPageIndex } );
	const selectElementById = useSelectElementById( { setSelectedElementIds } );
	const toggleElementIdInSelection = useToggleElementIdInSelection( { selectedElementIds, setSelectedElementIds } );
	const appendElementToCurrentPage = useAppendElementToCurrentPage( { currentPageIndex, pages, setPages, setSelectedElementIds } );
	const setPropertiesOnSelectedElements = useSetPropertiesOnSelectedElements( { currentPageIndex, pages, selectedElementIds, setPages } );

	useLoadStory( { storyId, pages, setPages, setCurrentPageIndex, clearSelection } );
	useHistoryEntry( { currentPageIndex, pages, selectedElementIds } );
	useHistoryReplay( { setCurrentPageIndex, setPages, setSelectedElementIds } );

	const state = {
		state: {
			pages,
			currentPageIndex,
			currentPageNumber,
			currentPage,
			selectedElementIds,
			selectedElements,
			hasSelection,
		},
		actions: {
			setCurrentPageByIndex,
			deletePageByIndex,
			addBlankPage,
			clearSelection,
			deleteSelectedElements,
			deleteCurrentPage,
			appendElementToCurrentPage,
			toggleElementIdInSelection,
			selectElementById,
			setPropertiesOnSelectedElements,
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
