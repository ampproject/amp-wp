/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { useAPI } from '../../api';

function useSavePost( {
	isSaving,
	storyId,
	title,
	postStatus,
	postAuthor,
	slug,
	pages,
	setLink,
	setPostStatus,
	setIsSaving,
} ) {
	const status = ( postStatus !== 'publish' ) ? 'publish' : postStatus;
	const { actions: { saveStoryById } } = useAPI();
	const savePost = useCallback( () => {
		if ( ! isSaving ) {
			setIsSaving( true );
			saveStoryById( storyId, title, status, pages, postAuthor, slug ).then( ( post ) => {
				const { status: thisPostStatus, link } = post;
				setIsSaving( false );
				setPostStatus( thisPostStatus );
				setLink( link );
			} );
		}
	}, [ isSaving, setIsSaving, saveStoryById, storyId, title, status, pages, postAuthor, slug, setPostStatus, setLink ] );

	return savePost;
}

export default useSavePost;
