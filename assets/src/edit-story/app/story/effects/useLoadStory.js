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
	setTitle,
	setPages,
	setPostStatus,
	setPostAuthor,
	setSlug,
	setLink,
	setCurrentPageIndex,
	clearSelection,
} ) {
	const { actions: { getStoryById } } = useAPI();
	const { actions: { clearHistory } } = useHistory();
	useEffect( () => {
		if ( storyId && pages.length === 0 ) {
			getStoryById( storyId ).then( ( post ) => {
				const { title: { raw: title }, status, author, slug, link } = post;
				let { meta: { amp_pages: newPages } } = post;

				// First clear history completely
				clearHistory();

				setTitle( title );
				setPostStatus( status );
				setPostAuthor( author );
				setSlug( slug );
				setLink( link );

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
	}, [ storyId, pages, getStoryById, clearHistory, setTitle, setPages, setCurrentPageIndex, clearSelection, setPostStatus, setPostAuthor, setSlug, setLink ] );
}

export default useLoadStory;
