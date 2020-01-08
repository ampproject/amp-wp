/**
 * WordPress dependencies
 */
import { useCallback, renderToString } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';
import { getDefinitionForType } from '../../../elements';

/**
 * Creates AMP HTML markup for saving to DB for rendering in the FE.
 *
 * @param {Object} pages Object of pages.
 * @return {Element} Markup of pages.
 */
const getStoryMarkupFromPages = ( pages ) => {
	const markup = pages.map( ( page ) => {
		const { id } = page;
		return renderToString(
			<amp-story-page id={ id }>
				<amp-story-grid-layer template="vertical">
					{ page.elements.map( ( { type, ...rest } ) => {
						const { id: elId } = rest;
						// eslint-disable-next-line @wordpress/no-unused-vars-before-return
						const { Save } = getDefinitionForType( type );
						return <Save key={ 'element-' + elId } { ...rest } />;
					} ) }
				</amp-story-grid-layer>
			</amp-story-page>,
		);
	} );
	return markup.join();
};

/**
 * Custom hook to save story.
 *
 * @param {Object}    properties Properties to update.
 * @param {number}    properties.storyId Story post id.
 * @param {Array}     properties.pages Array of all pages.
 * @param {Object}    properties.story Story-global properties
 * @return {Function} Function that can be called to save a story.
 */
function useSaveStory( {
	storyId,
	pages,
	story,
	updateStory,
} ) {
	const { actions: { saveStoryById } } = useAPI();

	/**
	 * Refresh page to edit url.
	 *
	 * @param {number} postId Current story id.
	 */
	const refreshPostEditURL = useCallback( ( postId ) => {
		const getPostEditURL = addQueryArgs( 'post.php', { post: postId, action: 'edit' } );
		window.history.replaceState(
			{ id: postId },
			'Post ' + postId,
			getPostEditURL,
		);
	}, [] );

	const saveStory = useCallback( () => {
		const { title, status: postStatus, author, slug } = story;
		const status = ( postStatus !== 'publish' ) ? 'publish' : postStatus;

		const content = getStoryMarkupFromPages( pages );
		saveStoryById( storyId, title, status, pages, author, slug, content ).then( ( post ) => {
			const { status: newStatus, link } = post;
			updateStory( {
				properties: {
					status: newStatus,
					link,
				},
			} );
			refreshPostEditURL( storyId );
		} ).catch( () => {
			// TODO Display error message to user as save as failed.
		} );
	}, [ storyId, pages, story, updateStory, saveStoryById, refreshPostEditURL ] );

	return saveStory;
}

export default useSaveStory;
