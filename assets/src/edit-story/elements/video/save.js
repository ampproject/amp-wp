/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function VideoSave( { autoPlay, id, mimeType, src, width, height, x, y, rotationAngle } ) {
	const sourceProps = {
		type: mimeType,
		src,
	};
	const props = {
		width,
		height,
		layout: 'fixed',
		id: 'el-' + id,
		autoPlay,
		poster: '/wp-content/plugins/amp/assets/images/stories-editor/story-fallback-poster.jpg', // @todo Replace this!
	};
	const style = {
		position: 'absolute',
		top: x + 'px',
		left: y + 'px',
		transform: `rotate(${ rotationAngle }deg)`,
	};
	return (
		<amp-video { ...props } style={ { ...style } }>
			<source { ...sourceProps } />
		</amp-video>
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
