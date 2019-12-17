/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../shared';
import { VideoWithScale } from './util';

const Element = styled.figure`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

const Video = styled.video.attrs( { muted: true } )`
	position: relative;
	${ VideoWithScale }
`;

function VideoDisplay( props ) {
	const {
		mimeType,
		src,
		forwardedRef,
		ampAriaLabel,
		videoCaption,
		loop,
		controls,
		autoPlay,
	} = props;

	return (
		<Element
			{ ...props }
			ref={ forwardedRef }>
			<Video

				aria-label={ ampAriaLabel }
				loop={ loop }
				controls={ controls }
				autoPlay={ autoPlay }
				muted={ true }
			>
				<source src={ src } type={ mimeType } />
			</Video>
			{ ( videoCaption ) && <figcaption>
				{ videoCaption }
			</figcaption> }
		</Element>
	);
}

VideoDisplay.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	controls: PropTypes.bool,
	autoPlay: PropTypes.bool,
	loop: PropTypes.bool,
	mimeType: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	videoCaption: PropTypes.string,
	ampAriaLabel: PropTypes.string,
	forwardedRef: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.func,
	] ),
};

VideoDisplay.defaultProps = {
	controls: false,
	loop: false,
	videoCaption: '',
	ampAriaLabel: '',
};

export default VideoDisplay;
