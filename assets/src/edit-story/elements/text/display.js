/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
} from '../shared';

const Element = styled.p`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }
`;

function TextDisplay( { content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle } ) {
	const props = {
		color,
		backgroundColor,
		fontFamily,
		fontStyle,
		fontSize,
		fontWeight,
		width,
		height,
		x,
		y,
	};
	return (
		<Element { ...props } dangerouslySetInnerHTML={ { __html: content } } />
	);
}

TextDisplay.propTypes = {
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontSize: PropTypes.string,
	fontWeight: PropTypes.string,
	fontStyle: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default TextDisplay;
