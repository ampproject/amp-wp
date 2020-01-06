/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize, ElementWithRotation, getBox } from '../shared';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
`;

const ActualBox = styled.div`
	width: 100%;
	height: 100%;
	position: relative;
	overflow: hidden;

	&::after {
		content: '';
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		border: 1px solid ${ ( { theme } ) => theme.colors.mg.v1 }70;
		pointer-events: none;
	}
`;

const FadedImg = styled.img`
	position: absolute;
	opacity: 0.4;
	${ ImageWithScale }
`;

const ActualImg = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageEdit( { src, origRatio, width, height, x, y, scale, focalX, focalY, rotationAngle, isFullbleed } ) {
	const elementProps = getBox( { x, y, width, height, rotationAngle, isFullbleed } );
	const imgProps = getImgProps( elementProps.width, elementProps.height, scale, focalX, focalY, origRatio );
	return (
		<Element { ...elementProps }>
			<FadedImg draggable={ false } src={ src } { ...imgProps } />
			<ActualBox>
				<ActualImg draggable={ false } src={ src } { ...imgProps } />
			</ActualBox>
		</Element>
	);
}

ImageEdit.propTypes = {
	src: PropTypes.string.isRequired,
	origRatio: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	scale: PropTypes.number,
	rotationAngle: PropTypes.number.isRequired,
	isFullbleed: PropTypes.bool,
	focalX: PropTypes.number,
	focalY: PropTypes.number,
};

ImageEdit.defaultProps = {
	scale: null,
	focalX: null,
	focalY: null,
};

export default ImageEdit;
