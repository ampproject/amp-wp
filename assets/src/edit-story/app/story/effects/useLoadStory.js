/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI, useHistory } from '../../';
import { createPage } from '../../../elements';

// When ID is set, load story from API.
function useLoadStory( {
	storyId,
	shouldLoad,
	restore,
} ) {
	const { actions: { getStoryById } } = useAPI();
	const { actions: { clearHistory } } = useHistory();
	useEffect( () => {
		if ( storyId && shouldLoad ) {
			getStoryById( storyId ).then( ( post ) => {
				const {
					title: { raw: title },
					status,
					author,
					slug,
					link,
					story_data: storyData,
				} = post;

				// First clear history completely.
				clearHistory();

				// Set story-global variables.
				const story = {
					title,
					status,
					author,
					slug,
					link,
				};

				// If there are no pages, create empty page.
				const pages = storyData.length === 0 ? [ createPage() ] : storyData;

				// TODO read current page and selection from deeplink?
				restore( {
					pages,
					story,
					selection: [],
					current: null, // will be set to first page by `restore`
				} );
			} );
		}
	}, [ storyId, shouldLoad, restore, getStoryById, clearHistory ] );
}

export default useLoadStory;
