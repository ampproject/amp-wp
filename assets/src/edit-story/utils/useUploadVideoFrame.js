/**
 * Internal dependencies
 */
/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { useAPI } from '../app/api';
import { useStory } from '../app/story';

function useUploadVideoFrame( videoId, src, id ) {
	const { actions: { uploadMedia, saveMedia } } = useAPI();

	const { actions: { updateElementById } } = useStory();
	const setProperties = useCallback(
		( properties ) => updateElementById( { elementId: id, properties } ),
		[ id, updateElementById ] );

	/**
	 * Returns an image of the first frame of a given video.
	 *
	 * @param {string} src Video src URL.
	 * @return {Promise<string>} The extracted image in base64-encoded format.
	 */
	const getFirstFrameOfVideo = useCallback( () => {
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
	}, [ src ] );

	const processData = async () => {
		try {
			const obj = await getFirstFrameOfVideo();
			const { id: featuredMedia, source_url: featuredMediaSrc } = await uploadMedia( obj );
			await saveMedia( featuredMedia, {
				meta: {
					amp_is_poster: true,
				},
			} );
			await saveMedia( videoId, {
				featured_media: featuredMedia,
			} );
			const newState = { featuredMedia, featuredMediaSrc };
			setProperties( newState );
		} catch ( err ) {
			// TODO Display error message to user as video poster upload has as failed.
		}
	};

	/**
	 * Uploads the video's first frame as an attachment.
	 *
	 */
	const uploadVideoFrame = useCallback( processData, [ getFirstFrameOfVideo, src, uploadMedia, saveMedia, videoId, setProperties ] );

	return {
		uploadVideoFrame,
	};
}

export default useUploadVideoFrame;
