/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getCommonAttributes } from '../shared';
import { PAGE_WIDTH } from '../../constants';
import { generateFontFamily } from './util';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function TextSave( { id, content, color, backgroundColor, width, height, x, y, fontFamily, fontFallback, fontSize, fontWeight, fontStyle, rotationAngle } ) {
	const getResponsiveFontSize = ( originalFontSize ) => {
		return ( ( originalFontSize / PAGE_WIDTH ) * 100 ).toFixed( 2 ) + 'vw';
	};

	const style = {
		...getCommonAttributes( { width, height, x, y, rotationAngle } ),
		fontSize: getResponsiveFontSize( fontSize ),
		fontStyle: fontStyle ? fontStyle : null,
		fontFamily: generateFontFamily( fontFamily, fontFallback ),
		fontWeight: fontWeight ? fontWeight : null,
		background: backgroundColor,
		color,
		lineHeight: 1.3,
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
