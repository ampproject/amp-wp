/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PanelTypes } from '../panels';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from './shared';

const Element = styled.img`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

function Image( { src, width, height, x, y, rotationAngle, forwardedRef, onPointerDown } ) {
	const props = {
		width,
		height,
		x,
		y,
		rotationAngle,
		src,
		ref: forwardedRef,
	};
	return (
		<Element draggable="false" { ...props } onPointerDown={ onPointerDown } />
	);
}

Image.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	forwardedRef: PropTypes.func,
	onPointerDown: PropTypes.func,
};

Image.defaultProps = {
};

Image.panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.ROTATION_ANGLE,
];

export default Image;
