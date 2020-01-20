/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';
import { useConfig } from '../../config';

/**
 * Custom hook to delete story.
 *
 * @param {Object}    properties Properties to delete.
 * @param {number}    properties.storyId Story post id.
 * @return {Function} Function that can be called to delete a story.
 */
function useDeleteStory( {
	storyId,
} ) {
	const { actions: { deleteStoryById } } = useAPI();
	const { postType } = useConfig();

	/**
	 * Refresh page to edit url.
	 *
	 * @param {number} postId Current story id.
	 */
	const refreshPostEditURL = useCallback( ( postId ) => {
		const getPostEditURL = addQueryArgs( 'edit.php', { trashed: 1, post_type: postType, ids: postId } );
		window.location.href = getPostEditURL;
	}, [ postType ] );

	const deleteStory = useCallback( () => {
		deleteStoryById( storyId ).then( () => {
			refreshPostEditURL( storyId );
		} ).catch( () => {
			// TODO Display error message to user as delete as failed.
		} );
	}, [ storyId, deleteStoryById, refreshPostEditURL ] );

	return { deleteStory };
}

export default useDeleteStory;
