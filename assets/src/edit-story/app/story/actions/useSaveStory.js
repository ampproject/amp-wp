/**
 * WordPress dependencies
 */
import { useCallback, renderToString, useState } from '@wordpress/element';
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
	const [ isSaving, setIsSaving ] = useState( false );

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
		setIsSaving( true );
		const { title, status, author, date, modified, slug, excerpt, featuredMedia } = story;

		const content = getStoryMarkupFromPages( pages );
		saveStoryById( storyId, title, status, pages, author, date, modified, slug, content, excerpt, featuredMedia ).then( ( post ) => {
			setIsSaving( false );
			const { status: newStatus, slug: newSlug, link } = post;
			updateStory( {
				properties: {
					status: newStatus,
					slug: newSlug,
					link,
				},
			} );
			refreshPostEditURL( storyId );
		} ).catch( () => {
			setIsSaving( false );
			// TODO Display error message to user as save as failed.
		} );
	}, [ storyId, pages, story, updateStory, saveStoryById, refreshPostEditURL ] );

	return { saveStory, isSaving };
}

export default useSaveStory;
