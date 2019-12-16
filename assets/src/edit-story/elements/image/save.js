/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function ImageSave( { id, src, width, height, x, y, rotationAngle } ) {
	const props = {
		width,
		height,
		layout: 'fixed',
		src,
	};
	const style = {
		position: 'absolute',
		top: x + 'px',
		left: y + 'px',
		transform: `rotate(${ rotationAngle }deg)`,
	};
	return (
		<amp-img id={ 'el-' + id } { ...props } style={ { ...style } } />
	);
}

ImageSave.propTypes = {
	id: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
};

export default ImageSave;
