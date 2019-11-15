/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PanelTypes } from '../panels';
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithFont,
	ElementWithBackgroundColor,
	ElementWithFontColor,
} from './shared';

const Element = styled.p`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }
`;

function Text( { content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle } ) {
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
		<Element { ...props }>
			{ content }
		</Element>
	);
}

Text.propTypes = {
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

Text.defaultProps = {
	fontFamily: 'Arial',
	fontWeight: 'normal',
	fontSize: 'auto',
	fontStyle: 'normal',
	color: 'black',
	backgroundColor: 'transparent',
};

Text.panels = [
	PanelTypes.TEXT,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.FONT,
	PanelTypes.COLOR,
	PanelTypes.BACKGROUND_COLOR,
];

export default Text;
