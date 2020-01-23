/**
 * External dependencies
 */
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
import StoryPropTypes from '../../types';
import { getImgProps, ImageWithScale } from './util';
import EditPanMovable from './editPanMovable';
import EditCropMovable from './editCropMovable';
import ScalePanel from './scalePanel';

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

function ImageEdit( {
	element: { id, src, origRatio, scale, focalX, focalY, isFullbleed },
	box: { x, y, width, height, rotationAngle },
} ) {
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

			<ScalePanel
				setProperties={ setProperties }
				x={ x }
				y={ y }
				width={ width }
				height={ height }
				scale={ scale || 100 } />
		</Element>
	);
}

ImageEdit.propTypes = {
	element: StoryPropTypes.elements.image.isRequired,
	box: StoryPropTypes.box.isRequired,
};

export default ImageEdit;
