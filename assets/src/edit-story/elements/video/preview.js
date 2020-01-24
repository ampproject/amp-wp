/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';

function VideoPreview( { id, mimeType, src, width, height, x, y, rotationAngle } ) {
	const sourceProps = {
		type: mimeType,
		src,
	};

	const wrapperProps = {
		id: 'el-' + id,
	};

	const style = getCommonAttributes( { width, height, x, y, rotationAngle } );
	return (
		<div style={ { ...style } } { ...wrapperProps } >
			<video { ...sourceProps } />
		</div>
	);
}

VideoPreview.propTypes = {
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

export default VideoPreview;
