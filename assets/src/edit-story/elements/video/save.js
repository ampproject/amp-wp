/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import getPercentageFromPixels from '../../utils/getPercentageFromPixels';

function VideoSave( { autoPlay, id, mimeType, src, width, height, x, y, rotationAngle } ) {
	const sourceProps = {
		type: mimeType,
		src,
	};
	const props = {
		autoPlay,
		poster: '/wp-content/plugins/amp/assets/images/stories-editor/story-fallback-poster.jpg', // @todo Replace this!
		layout: 'fill',
	};

	const wrapperProps = {
		id: 'el-' + id,
	};

	const style = {
		position: 'absolute',
		left: getPercentageFromPixels( x, 'x' ) + '%',
		top: getPercentageFromPixels( y, 'y' ) + '%',
		transform: `rotate(${ rotationAngle }deg)`,
		width: getPercentageFromPixels( width, 'x' ) + '%',
		height: getPercentageFromPixels( height, 'y' ) + '%',
	};
	return (
		<div style={ { ...style } } { ...wrapperProps } >
			<amp-video { ...props }>
				<source { ...sourceProps } />
			</amp-video>
		</div>
	);
}

VideoSave.propTypes = {
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
	id: PropTypes.string.isRequired,
};

export default VideoSave;
