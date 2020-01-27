/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function SquarePreview( { id, backgroundColor, width, height, x, y, rotationAngle } ) {
	const style = {
		...getCommonAttributes( { width, height, x, y, rotationAngle } ),
		background: backgroundColor,
	};
	return (
		<div id={ 'el-' + id } style={ { ...style } } />
	);
}

SquarePreview.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	id: PropTypes.string.isRequired,
};

export default SquarePreview;
