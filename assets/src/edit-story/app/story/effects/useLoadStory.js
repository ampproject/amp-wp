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
	setPages,
	setCurrentPageById,
	clearSelection,
} ) {
	const { getStoryById } = useAPI();
	const { actions: { clearHistory } } = useHistory();
	useEffect( () => {
		if ( storyId ) {
			getStoryById( storyId ).then( ( { content: { raw } } ) => {
				// First clear history completely
				clearHistory();

				// Then parse current story if any.
				let pages = null;
				try {
					pages = JSON.parse( raw );
				} catch {
					pages = [];
				}

				// If story is empty, create empty page and add to story:
				if ( pages.length === 0 ) {
					pages = [ createPage() ];
				}

				setPages( pages );
				// Mark first page as current
				// TODO read "current page" from deeplink if present?
				setCurrentPageById( pages[ 0 ].id );

				// TODO potentially also read selected elements from deeplink?
				clearSelection();
			} );
		}
	}, [ storyId, getStoryById, clearHistory, setPages, setCurrentPageById, clearSelection ] );
}

export default useLoadStory;
