/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';
import { generateFontFamily, getResponsiveFontSize } from './util';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function TextSave( { isPreview, id, content, color, backgroundColor, width, height, x, y, fontFamily, fontFallback, fontSize, fontWeight, fontStyle, rotationAngle } ) {
	const style = {
		...getCommonAttributes( { width, height, x, y, rotationAngle } ),
		fontSize: isPreview ? fontSize / 5.72 : getResponsiveFontSize( fontSize ),
		fontStyle: fontStyle ? fontStyle : null,
		fontFamily: generateFontFamily( fontFamily, fontFallback ),
		fontWeight: fontWeight ? fontWeight : null,
		background: backgroundColor,
		color,
		lineHeight: isPreview ? 1.3 / 1.77 : 1.3, // @todo This will be user-editable in the future.
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
	fontFallback: PropTypes.array,
	fontSize: PropTypes.number,
	fontWeight: PropTypes.number,
	fontStyle: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextSave;
