/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { ElementFillContent } from '../shared';
import useUploadVideoFrame from '../../utils/useUploadVideoFrame';

const Element = styled.video`
	${ ElementFillContent }
`;

function VideoDisplay( props ) {
	const {
		mimeType,
		src,
		videoId,
		featuredMedia,
	} = props;

	const { uploadVideoFrame } = useUploadVideoFrame( videoId, src );
	useEffect( () => {
		if ( ! featuredMedia ) {
			uploadVideoFrame();
		}
	},
	[ featuredMedia, uploadVideoFrame ],
	);

	return (
		<Element { ...props } >
			<source src={ src } type={ mimeType } />
		</Element>
	);
}

VideoDisplay.propTypes = {
	controls: PropTypes.bool,
	autoPlay: PropTypes.bool,
	loop: PropTypes.bool,
	mimeType: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	videoId: PropTypes.number,
	featuredMedia: PropTypes.number,
};

export default VideoDisplay;
