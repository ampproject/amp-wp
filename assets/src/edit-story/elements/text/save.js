/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function TextSave( { id, content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle, rotationAngle } ) {
	const style = {
		position: 'absolute',
		top: x + 'px',
		left: y + 'px',
		transform: `rotate(${ rotationAngle }deg)`,
		width: width + 'px',
		height: height + 'px',
		fontSize: fontSize ? fontSize : null,
		fontStyle: fontStyle ? fontStyle : null,
		fontFamily: fontFamily ? fontFamily : null,
		fontWeight: fontWeight ? fontWeight : null,
		background: backgroundColor,
		color,
	};

	return (
		<p id={ 'el-' + id } style={ { ...style } } >
			{ content }
		</p>
	);
}

TextSave.propTypes = {
	id: PropTypes.string.isRequired,
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontSize: PropTypes.string,
	fontWeight: PropTypes.string,
	fontStyle: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextSave;
