/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';

function VideoOutput( { autoPlay, id, mimeType, src, width, height, x, y, rotationAngle, isPreview } ) {
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

	const style = getCommonAttributes( { width, height, x, y, rotationAngle } );
	return (
		<div style={ { ...style } } { ...wrapperProps } >
			{ isPreview ? (
				<video { ...sourceProps } />
			) : (
				<amp-video { ...props }>
					<source { ...sourceProps } />
				</amp-video>
			) }
		</div>
	);
}

VideoOutput.propTypes = {
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
	isPreview: PropTypes.bool,
};

export default VideoOutput;
