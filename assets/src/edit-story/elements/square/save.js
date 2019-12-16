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
function SquareSave( { id, backgroundColor, width, height, x, y, rotationAngle } ) {
	const style = {
		position: 'absolute',
		left: getPercentageFromPixels( x, 'x' ) + '%',
		top: getPercentageFromPixels( y, 'y' ) + '%',
		transform: `rotate(${ rotationAngle }deg)`,
		width: getPercentageFromPixels( width, 'x' ) + '%',
		height: getPercentageFromPixels( height, 'y' ) + '%',
		background: backgroundColor,
	};
	return (
		<div id={ 'el-' + id } style={ { ...style } } />
	);
}

SquareSave.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	id: PropTypes.string.isRequired,
};

export default SquareSave;
