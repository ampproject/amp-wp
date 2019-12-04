/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../shared';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	overflow: hidden;
`;

const Img = styled.img`
	position: relative;
	${ ImageWithScale }
`;

function ImageDisplay( { src, origRatio, width, height, x, y, scale, offsetX, offsetY, rotationAngle } ) {
	// Width and height are percent of the actual page dimensions,
	// Thus 20-by-20 doesn't mean square, but "same as page ratio".
	const actualRatio = width / height * PAGE_WIDTH / PAGE_HEIGHT;
	const imgProps = getImgProps( scale, offsetX, offsetY, origRatio, actualRatio );
	const elementProps = {
		width,
		height,
		x,
		y,
		rotationAngle,
	};
	return (
		<Element { ...elementProps }>
			<Img src={ src } { ...imgProps } />
		</Element>
	);
}

ImageDisplay.propTypes = {
	src: PropTypes.string.isRequired,
	origRatio: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	scale: PropTypes.number,
	rotationAngle: PropTypes.number.isRequired,
	offsetX: PropTypes.number,
	offsetY: PropTypes.number,
};

ImageDisplay.defaultProps = {
	scale: null,
	offsetX: null,
	offsetY: null,
};

export default ImageDisplay;
