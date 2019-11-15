/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize, ElementWithBackgroundColor } from './shared';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithBackgroundColor }
`;

function Square( { backgroundColor, width, height, x, y } ) {
	const props = {
		backgroundColor,
		width,
		height,
		x,
		y,
	};
	return (
		<Element { ...props } />
	);
}

Square.propTypes = {
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

Square.defaultProps = {
	backgroundColor: 'hotpink',
};

Square.panels = [
	'size',
	'position',
	'backgroundColor',
];

export default Square;
