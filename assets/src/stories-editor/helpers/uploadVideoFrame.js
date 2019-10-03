/**
 * WordPress dependencies
 */
import { isBlobURL } from '@wordpress/blob';
import { dispatch, select } from '@wordpress/data';

const { getSettings } = select( 'core/block-editor' );
const { saveMedia } = dispatch( 'core' );

/**
 * Returns an image of the first frame of a given video.
 *
 * @param {string} src Video src URL.
 * @return {Promise<string>} The extracted image in base64-encoded format.
 */
const getFirstFrameOfVideo = ( src ) => {
	const video = document.createElement( 'video' );
	video.muted = true;
	video.crossOrigin = 'anonymous';
	video.preload = 'metadata';
	video.currentTime = 0.5; // Needed to seek forward.

	return new Promise( ( resolve, reject ) => {
		video.addEventListener( 'error', reject );

		video.addEventListener( 'canplay', () => {
			const canvas = document.createElement( 'canvas' );
			canvas.width = video.videoWidth;
			canvas.height = video.videoHeight;

			const ctx = canvas.getContext( '2d' );
			ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

			canvas.toBlob( resolve, 'image/jpeg' );
		} );

		video.src = src;
	} );
};

/**
 * Uploads the video's first frame as an attachment.
 *
 * @param {Object} media Media object.
 * @param {number} media.id  Video ID.
 * @param {string} media.src Video URL.
 */
const uploadVideoFrame = async ( { id: videoId, src } ) => {
	const { __experimentalMediaUpload: mediaUpload } = getSettings();

	const img = await getFirstFrameOfVideo( src );

	return new Promise( ( resolve, reject ) => {
		mediaUpload( {
			filesList: [ img ],
			onFileChange: ( [ fileObj ] ) => {
				const { id: posterId, url: posterUrl } = fileObj;

				if ( videoId && posterId ) {
					saveMedia( {
						id: videoId,
						featured_media: posterId,
					} );

					saveMedia( {
						id: posterId,
						meta: {
							amp_is_poster: true,
						},
					} );
				}

				if ( ! isBlobURL( posterUrl ) ) {
					resolve( fileObj );
				}
			},
			onError: reject,
		} );
	} );
};

export default uploadVideoFrame;
