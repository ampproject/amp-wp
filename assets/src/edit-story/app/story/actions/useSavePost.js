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
	setPostStatus,
	setIsSaving,
} ) {
	const status = ( postStatus !== 'publish' ) ? 'publish' : postStatus;
	const { actions: { saveStoryById } } = useAPI();
	const savePost = useCallback( () => {
		if ( ! isSaving ) {
			setIsSaving( true );
			saveStoryById( storyId, title, status, pages, postAuthor, slug ).then( () => {
				setIsSaving( false );
				setPostStatus( status );
			} );
		}
	}, [ isSaving, setIsSaving, storyId, title, pages, setPostStatus ] );

	return savePost;
}

export default useSavePost;
