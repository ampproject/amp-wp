/**
 * Internal dependencies
 */
import { IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from '../constants';

/**
 * Helper to process media object and return attributes to be saved.
 *
 * @param {Object} media Attachment object to be processed.
 *
 * @return {Object} Processed Object to save to block attributes.
 */
const processMedia = ( media ) => {
	if ( ! media || ! media.url ) {
		return {
			mediaUrl: undefined,
			mediaId: undefined,
			mediaType: undefined,
			mediaAlt: undefined,
			poster: undefined,
		};
	}

	let mediaType;

	// For media selections originated from a file upload.
	if ( media.media_type ) {
		if ( media.media_type === VIDEO_BACKGROUND_TYPE ) {
			mediaType = VIDEO_BACKGROUND_TYPE;
		} else {
			mediaType = IMAGE_BACKGROUND_TYPE;
		}
	} else {
		// For media selections originated from existing files in the media library.
		if (
			media.type !== IMAGE_BACKGROUND_TYPE &&
			media.type !== VIDEO_BACKGROUND_TYPE
		) {
			return {
				mediaUrl: undefined,
				mediaId: undefined,
				mediaType: undefined,
				mediaAlt: undefined,
				poster: undefined,
			};
		}

		mediaType = media.type;
	}

	const mediaAlt = media.alt || media.title;
	const mediaUrl = media.url;
	const poster = VIDEO_BACKGROUND_TYPE === mediaType && media.image && media.image.src !== media.icon ? media.image.src : undefined;

	return {
		mediaUrl,
		mediaId: media.id,
		mediaType,
		mediaAlt,
		poster,
	};
};

export default processMedia;
