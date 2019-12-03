/**
 * WordPress dependencies
 */
import { useCallback, renderToString } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';

const getStoryMarkupFromPages = ( pages ) => {
	// @todo look into: We should rather use renderToStaticMarkup here, however, it doesn't seem to be exposed via @wordpress/element.
	const markup = pages.map( ( page ) => {
		const { id } = page;
		return renderToString(
			<amp-story-page id={ id }>
				<amp-story-grid-layer template="vertical">
					{ page.elements.map( ( { type, src, width, height, content } ) => {
						const style = {
							position: 'absolute',
						};
						// @todo this should be redone by using dynamic tag.
						if ( 'image' === type ) {
							return <amp-img style={ { ...style } } src={ src } layout="fixed" width={ width } height={ height } />;
						}
						style.width = width + 'px';
						style.height = height + 'px';
						return (
							<div style={ { ...style } }>
								{ content }
							</div>
						);
					} ) }
				</amp-story-grid-layer>
			</amp-story-page>,
		);
	} );
	return markup.join();
};

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
			const content = getStoryMarkupFromPages( pages );
			saveStoryById( storyId, title, status, pages, postAuthor, slug, content ).then( ( post ) => {
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
