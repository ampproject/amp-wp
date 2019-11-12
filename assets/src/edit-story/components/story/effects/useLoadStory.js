/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { rawHandler, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';

// When ID is set, load story from API.
function useLoadStory( storyId, setPages, setCurrentPageById ) {
	const { getStoryById } = useAPI();
	useEffect( () => {
		if ( storyId ) {
			getStoryById( storyId ).then( ( { content: { raw } } ) => {
				const rootBlocks = rawHandler( { HTML: raw } );

				// If story is empty, create empty page and add to story:
				if ( rootBlocks.length === 0 ) {
					const firstPage = createBlock( 'amp/amp-story-page' );
					rootBlocks.push( firstPage );
				}

				setPages( rootBlocks );
				// Mark first page as current
				// TODO read "current page" from deeplink if present
				setCurrentPageById( rootBlocks[ 0 ].clientId );
			} );
		}
	}, [ storyId, getStoryById, setPages, setCurrentPageById ] );
}

export default useLoadStory;
