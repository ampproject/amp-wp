/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ElementFillContent } from '../shared';
import { useStory } from '../../app';
import EditPanMovable from './editPanMovable';
import EditCropMovable from './editCropMovable';
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementFillContent }
`;

const CropBox = styled.div`
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
	pointer-events: none;
	${ ImageWithScale }
`;

const CropImg = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageEdit( { id, src, origRatio, width, height, x, y, scale, focalX, focalY, rotationAngle, isFullbleed } ) {
	const [ fullImage, setFullImage ] = useState( null );
	const [ croppedImage, setCroppedImage ] = useState( null );
	const [ cropBox, setCropBox ] = useState( null );

	const { actions: { updateElementById } } = useStory();
	const setProperties = useCallback(
		( properties ) => updateElementById( { elementId: id, properties } ),
		[ id, updateElementById ] );

	const imgProps = getImgProps( width, height, scale, focalX, focalY, origRatio );

	return (
		<Element>
			<FadedImg ref={ setFullImage } draggable={ false } src={ src } { ...imgProps } />
			<CropBox ref={ setCropBox }>
				<CropImg ref={ setCroppedImage } draggable={ false } src={ src } { ...imgProps } />
			</CropBox>

			{ ! isFullbleed && cropBox && croppedImage && (
				<EditCropMovable
					setProperties={ setProperties }
					cropBox={ cropBox }
					croppedImage={ croppedImage }
					x={ x }
					y={ y }
					offsetX={ imgProps.offsetX }
					offsetY={ imgProps.offsetY }
					imgWidth={ imgProps.width }
					imgHeight={ imgProps.height }
				/>
			) }

			{ fullImage && croppedImage && (
				<EditPanMovable
					setProperties={ setProperties }
					fullImage={ fullImage }
					croppedImage={ croppedImage }
					x={ x }
					y={ y }
					width={ width }
					height={ height }
					rotationAngle={ rotationAngle }
					offsetX={ imgProps.offsetX }
					offsetY={ imgProps.offsetY }
					imgWidth={ imgProps.width }
					imgHeight={ imgProps.height }
				/>
			) }
		</Element>
	);
}

ImageEdit.propTypes = {
	id: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	origRatio: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	isFullbleed: PropTypes.bool,
	scale: PropTypes.number,
	focalX: PropTypes.number,
	focalY: PropTypes.number,
};

ImageEdit.defaultProps = {
	scale: null,
	focalX: null,
	focalY: null,
};

export default ImageEdit;
