/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';
import { generateFontFamily } from './util';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function TextPreview( {
	id,
	content,
	color,
	backgroundColor,
	width,
	height,
	x,
	y,
	fontFamily,
	fontFallback,
	fontSize,
	fontWeight,
	fontStyle,
	letterSpacing,
	lineHeight,
	padding,
	rotationAngle,
	textAlign,
	previewSizeMultiplier,
} ) {
	const style = {
		...getCommonAttributes( { width, height, x, y, rotationAngle } ),
		fontSize: Math.ceil( fontSize * previewSizeMultiplier ),
		fontStyle: fontStyle ? fontStyle : null,
		fontFamily: generateFontFamily( fontFamily, fontFallback ),
		fontWeight: fontWeight ? fontWeight : null,
		background: backgroundColor,
		margin: 0,
		color,
		lineHeight,
		letterSpacing: letterSpacing ? letterSpacing + 'em' : null,
		padding: padding ? padding + '%' : null,
		textAlign: textAlign ? textAlign : null,
	};

	return (
		<p id={ 'el-' + id } style={ { ...style } } dangerouslySetInnerHTML={ { __html: content } } />
	);
}

TextPreview.propTypes = {
	id: PropTypes.string.isRequired,
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontFallback: PropTypes.array,
	fontSize: PropTypes.number,
	fontWeight: PropTypes.number,
	fontStyle: PropTypes.string,
	letterSpacing: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
	lineHeight: PropTypes.number,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	padding: PropTypes.number,
	rotationAngle: PropTypes.number.isRequired,
	textAlign: PropTypes.string,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	previewSizeMultiplier: PropTypes.number,
};

export default TextPreview;
