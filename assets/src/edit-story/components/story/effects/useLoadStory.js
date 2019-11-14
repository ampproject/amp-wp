/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { rawHandler, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';
import useHistory from '../../history';

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
				const rootBlocks = rawHandler( { HTML: raw } );

				// If story is empty, create empty page and add to story:
				if ( rootBlocks.length === 0 ) {
					const firstPage = createBlock( 'amp/amp-story-page' );
					rootBlocks.push( firstPage );
				}

				setPages( rootBlocks );
				// Mark first page as current
				// TODO read "current page" from deeplink if present?
				setCurrentPageById( rootBlocks[ 0 ].clientId );

				// TODO potentially also read selected elements from deeplink?
				clearSelection();
			} );
		}
	}, [ storyId, getStoryById, clearHistory, setPages, setCurrentPageById, clearSelection ] );
}

export default useLoadStory;
