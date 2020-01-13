/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI, useHistory } from '../../';
import { createPage } from '../../../elements';

const getPerm = ( post, field ) => {
	return Boolean( get( post, [ '_links', field ], false ) );
};

// When ID is set, load story from API.
function useLoadStory( {
	storyId,
	shouldLoad,
	restore,
} ) {
	const { actions: { getStoryById } } = useAPI();
	const { actions: { clearHistory } } = useHistory();

	useEffect( () => {
		if ( storyId && shouldLoad ) {
			getStoryById( storyId ).then( ( post ) => {
				const {
					title: { raw: title },
					status,
					author,
					slug,
					date,
					modified,
					excerpt: { raw: excerpt },
					link,
					story_data: storyData,
					featured_media: featuredMedia,
					featured_media_url: featuredMediaUrl,
				} = post;

				const statusFormat = ( status === 'auto-draft' ) ? 'draft' : status;

				// First clear history completely.
				clearHistory();

				// Set story-global variables.
				const story = {
					title,
					status: statusFormat,
					author,
					date,
					modified,
					excerpt,
					slug,
					link,
					featuredMedia,
					featuredMediaUrl,
				};

				// If there are no pages, create empty page.
				const pages = storyData.length === 0 ? [ createPage() ] : storyData;

				const hasPublishAction = getPerm( post, 'wp:action-publish' );
				const hasAssignAuthorAction = getPerm( post, 'wp:action-assign-author' );

				const capabilities = { hasPublishAction, hasAssignAuthorAction };
				// TODO read current page and selection from deeplink?
				restore( {
					pages,
					story,
					selection: [],
					current: null, // will be set to first page by `restore`
					capabilities,
				} );
			} );
		}
	}, [ storyId, shouldLoad, restore, getStoryById, clearHistory ] );
}

export default useLoadStory;
