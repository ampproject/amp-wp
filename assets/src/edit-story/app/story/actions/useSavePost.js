/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

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

	const refreshPostEditURL = ( postId ) => {
		const getPostEditURL = addQueryArgs( 'post.php', { post: postId, action: 'edit' } );
		window.history.replaceState(
			{ id: postId },
			'Post ' + postId,
			getPostEditURL,
		);
	};

	const savePost = useCallback( () => {
		if ( ! isSaving ) {
			setIsSaving( true );
			saveStoryById( storyId, title, status, pages, postAuthor, slug ).then( ( post ) => {
				const { status: thisPostStatus, link } = post;
				setIsSaving( false );
				setPostStatus( thisPostStatus );
				setLink( link );
				refreshPostEditURL( storyId );
			} );
		}
	}, [ isSaving, setIsSaving, saveStoryById, storyId, title, status, pages, postAuthor, slug, setPostStatus, setLink ] );

	return savePost;
}

export default useSavePost;
