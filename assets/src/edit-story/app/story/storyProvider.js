/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

import useLoadStory from './effects/useLoadStory';
import useSaveStory from './actions/useSaveStory';
import useHistoryEntry from './effects/useHistoryEntry';
import useHistoryReplay from './effects/useHistoryReplay';
import useStoryReducer from './useStoryReducer';
import useDeleteStory from './actions/useDeleteStory';

function StoryProvider( { storyId, children } ) {
	const {
		state: {
			pages,
			current,
			selection,
			story,
			capabilities,
		},
		api,
		internal: {
			restore,
		},
	} = useStoryReducer();

	// Generate current page info.
	const {
		currentPageId,
		currentPageIndex,
		currentPageNumber,
		currentPage,
	} = useMemo( () => {
		if ( ! current ) {
			return {
				currentPageId: null,
				currentPageIndex: null,
				currentPageNumber: null,
				currentPage: null,
			};
		}
		const index = pages.findIndex( ( { id } ) => id === current );
		const number = index + 1;
		const page = pages[ index ];
		return {
			currentPageId: current,
			currentPageIndex: index,
			currentPageNumber: number,
			currentPage: page,
		};
	}, [ pages, current ] );

	// Generate selection info
	const {
		selectedElementIds,
		selectedElements,
		hasSelection,
	} = useMemo(
		() => {
			if ( ! currentPage ) {
				return {
					selectedElements: [],
					selectedElementIds: [],
					hasSelection: false,
				};
			}
			const els = currentPage.elements.filter( ( { id } ) => selection.includes( id ) );
			return {
				selectedElementIds: selection,
				selectedElements: els,
				hasSelection: els.length > 0,
			};
		},
		[ currentPage, selection ],
	);

	// This effect loads and initialises the story on first load (when there's no pages).
	const shouldLoad = pages.length === 0;
	useLoadStory( { restore, shouldLoad, storyId } );

	// These effects send updates to and restores state from history.
	useHistoryEntry( { pages, current, selection, story, capabilities } );
	useHistoryReplay( { restore } );

	// This action allows the user to save the story
	// (and it will have side-effects because saving can update url and status,
	//  thus the need for `updateStory`)
	const { updateStory } = api;
	const { saveStory, isSaving } = useSaveStory( { storyId, pages, story, updateStory } );
	const { deleteStory } = useDeleteStory( { storyId } );

	const state = {
		state: {
			pages,
			currentPageId,
			currentPageIndex,
			currentPageNumber,
			currentPage,
			selectedElementIds,
			selectedElements,
			hasSelection,
			story,
			capabilities,
			meta: {
				isSaving,
			},
		},
		actions: {
			...api,
			saveStory,
			deleteStory,
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
