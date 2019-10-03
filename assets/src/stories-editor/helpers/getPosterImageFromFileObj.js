/**
 * External dependencies
 */
import { has } from 'lodash';

/**
 * Internal dependencies
 */
import { MAX_IMAGE_SIZE_SLUG } from '../constants';

/**
 * Given a media object, returns a suitable poster image URL.
 *
 * @param {Object} fileObj Media object.
 * @return {string} Poster image URL.
 */
const getPosterImageFromFileObj = ( fileObj ) => {
	const { url } = fileObj;

	let newPoster = url;

	if ( has( fileObj, [ 'media_details', 'sizes', MAX_IMAGE_SIZE_SLUG, 'source_url' ] ) ) {
		newPoster = fileObj.media_details.sizes[ MAX_IMAGE_SIZE_SLUG ].source_url;
	} else if ( has( fileObj, [ 'media_details', 'sizes', 'large', 'source_url' ] ) ) {
		newPoster = fileObj.media_details.sizes.large.source_url;
	}

	return newPoster;
};

export default getPosterImageFromFileObj;
