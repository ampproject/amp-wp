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
	pages,
	setPages,
	setCurrentPageIndex,
	clearSelection,
} ) {
	const { getStoryById } = useAPI();
	const { actions: { clearHistory } } = useHistory();
	useEffect( () => {
		if ( storyId && pages.length === 0 ) {
			getStoryById( storyId ).then( ( { content: { raw } } ) => {
				// First clear history completely
				clearHistory();

				// Then parse current story if any.
				let newPages = null;
				try {
					newPages = JSON.parse( raw );
				} catch {
					newPages = [];
				}

				// If story is empty, create empty page and add to story:
				if ( newPages.length === 0 ) {
					newPages = [ createPage( { index: 0 } ) ];
				}

				setPages( newPages );
				// Mark first page as current
				// TODO read "current page" from deeplink if present?
				setCurrentPageIndex( 0 );

				// TODO potentially also read selected elements from deeplink?
				clearSelection();
			} );
		}
	}, [ storyId, pages, getStoryById, clearHistory, setPages, setCurrentPageIndex, clearSelection ] );
}

export default useLoadStory;
