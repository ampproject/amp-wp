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
	ElementWithRotation,
} from './shared';

const Element = styled.p`
	margin: 0;
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithFont }
	${ ElementWithBackgroundColor }
	${ ElementWithFontColor }
`;

function Text( { content, color, backgroundColor, width, height, x, y, fontFamily, fontSize, fontWeight, fontStyle, rotationAngle, forwardedRef, onPointerDown } ) {
	const props = {
		color,
		backgroundColor,
		fontFamily,
		fontStyle,
		fontSize,
		fontWeight,
		ref: forwardedRef,
		width,
		height,
		rotationAngle,
		x,
		y,
	};
	return (
		<Element draggable="false" { ...props } onPointerDown={ onPointerDown }>
			{ content }
		</Element>
	);
}

Text.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	content: PropTypes.string,
	color: PropTypes.string,
	backgroundColor: PropTypes.string,
	fontFamily: PropTypes.string,
	fontSize: PropTypes.string,
	fontWeight: PropTypes.string,
	fontStyle: PropTypes.string,
	forwardedRef: PropTypes.func,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	onPointerDown: PropTypes.func,
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
	PanelTypes.ROTATION_ANGLE,
	PanelTypes.POSITION,
	PanelTypes.FONT,
	PanelTypes.COLOR,
	PanelTypes.BACKGROUND_COLOR,
];

export default Text;
