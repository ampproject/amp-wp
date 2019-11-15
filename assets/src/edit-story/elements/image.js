/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize } from './shared';

const Element = styled.img`
	${ ElementWithPosition }
	${ ElementWithSize }
`;

function Image( { src, width, height, x, y } ) {
	const props = {
		width,
		height,
		x,
		y,
		src,
	};
	return (
		<Element { ...props } />
	);
}

Image.propTypes = {
	src: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
};

Image.defaultProps = {
};

Image.panels = [
	'size',
	'position',
];

export default Image;
