/**
 * Internal dependencies
 */
import { useAPI } from '../app/api';

function useUploadVideoFrame( videoId, src ) {
	const { actions: { uploadMedia, saveMedia } } = useAPI();

	/**
	 * Returns an image of the first frame of a given video.
	 *
	 * @param {string} src Video src URL.
	 * @return {Promise<string>} The extracted image in base64-encoded format.
	 */
	const getFirstFrameOfVideo = () => {
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
	const uploadVideoFrame = async () => {
		await getFirstFrameOfVideo( src ).then( ( obj ) => uploadMedia( obj ).then( ( { id: posterId } ) => {
			saveMedia( videoId, {
				featured_media: posterId,
			} );
		} ) );
	};

	return {
		uploadVideoFrame,
	};
}

export default useUploadVideoFrame;
