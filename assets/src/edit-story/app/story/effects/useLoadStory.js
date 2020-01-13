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
import { useAPI, useConfig, useHistory } from '../../';
import { createPage } from '../../../elements';

const getTaxPerms = ( restBase, post ) => {
	return {
		hasCreateAction: getPerm( post, 'wp:action-create-' + restBase ),
		hasAssignAction: getPerm( post, 'wp:action-assign-' + restBase ),
	};
};

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
	const { taxonomies, postThumbnails } = useConfig();

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
				};

				// If there are no pages, create empty page.
				const pages = storyData.length === 0 ? [ createPage() ] : storyData;

				const hasPublishAction = getPerm( post, 'wp:action-publish' );
				const hasAssignAuthorAction = getPerm( post, 'wp:action-assign-author' );
				const termPerm = {};
				taxonomies.forEach( ( { name, rest_base: restBase } ) => {
					termPerm[ name ] = getTaxPerms( restBase, post );
				} );

				const capabilities = { hasPublishAction, hasAssignAuthorAction, termPerm, postThumbnails };
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
	}, [ storyId, shouldLoad, restore, getStoryById, clearHistory, taxonomies ] );
}

export default useLoadStory;
