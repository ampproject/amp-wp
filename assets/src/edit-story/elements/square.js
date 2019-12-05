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
	ElementWithBackgroundColor,
	ElementWithRotation,
} from './shared';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithBackgroundColor }
`;

function Square( { backgroundColor, width, height, x, y, rotationAngle, forwardedRef } ) {
	const props = {
		backgroundColor,
		width,
		height,
		rotationAngle,
		x,
		y,
		ref: forwardedRef,
	};
	return (
		<Element { ...props } />
	);
}

Square.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	forwardedRef: PropTypes.func,
};

Square.defaultProps = {
	backgroundColor: 'hotpink',
};

Square.panels = [
	PanelTypes.SIZE,
	PanelTypes.ROTATION_ANGLE,
	PanelTypes.POSITION,
	PanelTypes.BACKGROUND_COLOR,
];

export default Square;
