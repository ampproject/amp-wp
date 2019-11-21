/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import { ElementWithPosition, ElementWithSize } from '../shared';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
`;

const ActualBox = styled.div`
	width: 100%;
	height: 100%;
	border: 1px solid ${ ( { theme } ) => theme.colors.mg.v1 }70;
	overflow: hidden;
`;

const FadedImg = styled.img`
	position: absolute;
	opacity: 0.4;
	${ ImageWithScale }
`;

const ActualImg = styled.img`
	position: relative;
	${ ImageWithScale }
`;

function ImageEdit( { src, origRatio, width, height, x, y, scale, offsetX, offsetY } ) {
	const actualRatio = width / height * PAGE_WIDTH / PAGE_HEIGHT;
	const imgProps = getImgProps( scale, offsetX, offsetY, origRatio, actualRatio );
	const elementProps = { width, height, x, y };
	return (
		<Element { ...elementProps }>
			<FadedImg src={ src } { ...imgProps } />
			<ActualBox>
				<ActualImg src={ src } { ...imgProps } />
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
	offsetX: PropTypes.number,
	offsetY: PropTypes.number,
};

ImageEdit.defaultProps = {
	scale: null,
	offsetX: null,
	offsetY: null,
};

export default ImageEdit;
