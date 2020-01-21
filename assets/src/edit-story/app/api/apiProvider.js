/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { useConfig } from '../';
import Context from './context';

function APIProvider( { children } ) {
	const { api: { stories, media, fonts, users, statuses } } = useConfig();

	const getStoryById = useCallback(
		( storyId ) => {
			const path = addQueryArgs( `${ stories }/${ storyId }`, { context: `edit` } );
			return apiFetch( { path } );
		},
		[ stories ],
	);

	const saveStoryById = useCallback(
		/**
		 * Fire REST API call to save story.
		 *
		 * @param {Object} 	 story - A story object.
		 * @param {number}   story.storyId Story post id.
		 * @param {string}   story.title Story title.
		 * @param {string}   story.status Post status, draft or published.
		 * @param {Array}    story.pages Array of all pages.
		 * @param {number}   story.author User ID of story author.
		 * @param {string}   story.slug   The slug of the story.
		 * @param {string}   story.date   The publish date of the story.
		 * @param {string}   story.modified   The modified date of the story.
		 * @param {string}   story.content AMP HTML content.
		 * @param {string}   story.excerpt Short description.
		 * @param {number}   story.featuredMedia Featured image id.
		 * @param {string}   story.password Password
		 * @return {Promise} Return apiFetch promise.
		 */
		( { storyId, title, status, pages, author, slug, date, modified, content, excerpt, featuredMedia, password } ) => {
			return apiFetch( {
				path: `${ stories }/${ storyId }`,
				data: {
					title,
					status,
					author,
					password,
					slug,
					date,
					modified,
					content,
					excerpt,
					story_data: pages,
					featured_media: featuredMedia,
				},
				method: 'POST',
			} );
		},
		[ stories ],
	);

	const deleteStoryById = useCallback(
		/**
		 * Fire REST API call to delete story.
		 *
		 * @param {number}   storyId Story post id.
		 * @return {Promise} Return apiFetch promise.
		 */
		( storyId ) => {
			return apiFetch( {
				path: `${ stories }/${ storyId }`,
				method: 'DELETE',
			} );
		},
		[ stories ],
	);

	const getMedia = useCallback(
		( { mediaType, searchTerm } ) => {
			let apiPath = media;
			const perPage = 100;
			apiPath = addQueryArgs( apiPath, { per_page: perPage } );

			if ( mediaType ) {
				apiPath = addQueryArgs( apiPath, { media_type: mediaType } );
			}

			if ( searchTerm ) {
				apiPath = addQueryArgs( apiPath, { search: searchTerm } );
			}

			return apiFetch( { path: apiPath } )
				.then( ( data ) => data.map(
					( {
						id,
						guid: { rendered: src },
						media_details: { width: oWidth, height: oHeight },
						mime_type: mimeType,
						featured_media: featuredMedia,
						featured_media_src: featuredMediaSrc,
					} ) => ( {
						id,
						featuredMedia,
						featuredMediaSrc,
						src,
						oWidth,
						oHeight,
						mimeType,
					} ),
				) );
		},	[ media ],
	);

	/**
	 * @param {File}    file           Media File to Save.
	 * @param {?Object} additionalData Additional data to include in the request.
	 *
	 * @return {Promise} Media Object Promise.
	 */
	const uploadMedia = useCallback(
		( file ) => {
			// Create upload payload
			const data = new window.FormData();
			data.append( 'file', file, file.name || file.type.replace( '/', '.' ) );
			return apiFetch( {
				path: media,
				body: data,
				method: 'POST',
			} );
		}, [ media ],
	);

	const saveMedia = useCallback(
		( mediaId, data ) => {
			return apiFetch( {
				path: `${ media }/${ mediaId }`,
				data,
				method: 'POST',
			} );
		},
		[ media ],
	);

	const getAllFonts = useCallback(
		( {} ) => {
			return apiFetch( { path: fonts } )
				.then( ( data ) => data.map(
					( font ) => ( {
						thisValue: font.name,
						...font,
					} ),
				) );
		}, [ fonts ],
	);

	const getAllStatuses = useCallback(
		() => {
			const path = addQueryArgs( statuses, { context: `edit` } );
			return apiFetch( { path } );
		}, [ statuses ],
	);

	const getAllUsers = useCallback(
		() => {
			return apiFetch( { path: users } );
		}, [ users ],
	);

	const state = {
		actions: {
			getStoryById,
			getMedia,
			saveStoryById,
			deleteStoryById,
			getAllFonts,
			getAllStatuses,
			getAllUsers,
			uploadMedia,
			saveMedia,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

APIProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default APIProvider;
