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
function TextSave( { id, content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle, rotationAngle } ) {
	const style = {
		...getCommonAttributes( { width, height, x, y, rotationAngle } ),
		fontSize: `${ fontSize }px`,
		fontStyle: fontStyle ? fontStyle : null,
		fontFamily: fontFamily ? fontFamily : null,
		fontWeight: fontWeight ? fontWeight : null,
		background: backgroundColor,
		color,
	};

	return (
		<p id={ 'el-' + id } style={ { ...style } } dangerouslySetInnerHTML={ { __html: content } } />
	);
}

TextSave.propTypes = {
	id: PropTypes.string.isRequired,
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontSize: PropTypes.string,
	fontWeight: PropTypes.number,
	fontStyle: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextSave;
