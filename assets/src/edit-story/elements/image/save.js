/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import getPercentageFromPixels from '../../utils/getPercentageFromPixels';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function ImageSave( { id, src, width, height, x, y, rotationAngle } ) {
	const props = {
		layout: 'fill',
		src,
	};
	const wrapperProps = {
		id: 'el-' + id,
	};
	const style = {
		position: 'absolute',
		left: getPercentageFromPixels( x, 'x' ) + '%',
		top: getPercentageFromPixels( y, 'y' ) + '%',
		width: getPercentageFromPixels( width, 'x' ) + '%',
		height: getPercentageFromPixels( height, 'y' ) + '%',
		transform: `rotate(${ rotationAngle }deg)`,
	};

	return (
		<div style={ { ...style } } { ...wrapperProps }>
			<amp-img { ...props } />
		</div>
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
