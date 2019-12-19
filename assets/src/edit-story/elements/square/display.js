/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	ElementWithPosition,
	ElementWithSize,
	ElementWithBackgroundColor,
	ElementWithRotation,
	updateMovableTargets,
} from '../shared';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	${ ElementWithBackgroundColor }
`;

function SquareDisplay( { id, backgroundColor, width, height, x, y, rotationAngle, forwardedRef, setTargetRefs } ) {
	const element = useRef();
	const props = {
		backgroundColor,
		width,
		height,
		rotationAngle,
		x,
		y,
		ref: forwardedRef ? forwardedRef : element,
	};
	useLayoutEffect( () => {
		updateMovableTargets( element, id, setTargetRefs, forwardedRef, x, y, rotationAngle );
	}, [ id, setTargetRefs, forwardedRef, x, y, rotationAngle ] );
	return (
		<Element { ...props } />
	);
}

SquareDisplay.propTypes = {
	id: PropTypes.string.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	forwardedRef: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.func,
	] ),
	setTargetRefs: PropTypes.func.isRequired,
};

export default SquareDisplay;
