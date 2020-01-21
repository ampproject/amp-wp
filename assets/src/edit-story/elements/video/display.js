/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
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
		id,
	} = props;

	const { uploadVideoFrame } = useUploadVideoFrame( videoId, src, id );
	useEffect( () => {
		if ( videoId && ! featuredMedia ) {
			uploadVideoFrame();
		}
	},
	[ videoId, featuredMedia, uploadVideoFrame ],
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
	videoId: PropTypes.number.isRequired,
	featuredMedia: PropTypes.number,
	id: PropTypes.string.isRequired,
};

export default VideoDisplay;
