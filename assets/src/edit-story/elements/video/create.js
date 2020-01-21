/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import useUploadVideoFrame from '../../utils/useUploadVideoFrame';

function VideoCreate( props ) {
	const {
		src,
		videoId,
		featuredMedia,
	} = props;

	const { uploadVideoFrame } = useUploadVideoFrame( videoId, src );
	if ( ! featuredMedia ) {
		uploadVideoFrame();
	}
}

VideoCreate.propTypes = {
	src: PropTypes.string.isRequired,
	videoId: PropTypes.number,
	featuredMedia: PropTypes.number,
};

export default VideoCreate;
