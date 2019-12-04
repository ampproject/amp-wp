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
	ElementWithBackgroundColor,
	ElementWithRotation,
} from '../shared';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithBackgroundColor }
`;

function SquareDisplay( { backgroundColor, width, height, x, y, rotationAngle } ) {
	const props = {
		backgroundColor,
		width,
		height,
		rotationAngle,
		x,
		y,
	};
	return (
		<Element { ...props } />
	);
}

SquareDisplay.propTypes = {
	rotationAngle: PropTypes.number.isRequired,
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

export default SquareDisplay;
