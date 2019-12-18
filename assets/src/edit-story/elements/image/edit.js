/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ElementWithPosition, ElementWithSize, ElementWithRotation, getBox } from '../shared';
import { useStory } from '../../app';
import Movable from '../../components/movable';
import { getFocalFromOffset, getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
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

	const { actions: { setPropertiesById } } = useStory();

	const elementProps = getBox( { x, y, width, height, rotationAngle, isFullbleed } );
	const imgProps = getImgProps( elementProps.width, elementProps.height, scale, focalX, focalY, origRatio );

	const resizeMoveableRef = useRef();
	const panMoveableRef = useRef();
	const translateRef = useRef( [ 0, 0 ] );
	const cropRef = useRef( [ 0, 0, 0, 0 ] );

	const updatePan = () => {
		const [ tx, ty ] = translateRef.current;
		fullImage.style.transform = `translate(${ tx }px, ${ ty }px)`;
		croppedImage.style.transform = `translate(${ tx }px, ${ ty }px)`;
	};

	// Refresh moveables to ensure that the selection rect is always correct.
	useEffect( () => {
		if ( resizeMoveableRef.current ) {
			resizeMoveableRef.current.updateRect();
		}
		if ( panMoveableRef.current ) {
			panMoveableRef.current.updateRect();
		}
	} );

	return (
		<Element { ...elementProps }>
			<FadedImg ref={ setFullImage } draggable={ false } src={ src } { ...imgProps } />
			<CropBox ref={ setCropBox }>
				<CropImg ref={ setCroppedImage } draggable={ false } src={ src } { ...imgProps } />
			</CropBox>

			{ /* Resizable moveable for cropping */ }
			{ ! isFullbleed && (
				<Movable
					ref={ resizeMoveableRef }
					className="crop-movable"
					targets={ cropBox }

					origin={ false }
					edge={ false }

					draggable={ false }
					scalable={ false }
					rotatable={ false }
					warpable={ false }
					pinchable={ false }

					resizable={ true }
					onResize={ ( { width, height, delta, drag } ) => {
	            const [ tx, ty ] = [ drag.beforeTranslate[ 0 ], drag.beforeTranslate[ 1 ] ];
	            cropBox.style.transform = `translate(${ tx }px, ${ ty }px)`;
	            croppedImage.style.transform = `translate(${ -tx }px, ${ -ty }px)`;
	            if ( delta[ 0 ] ) {
	              cropBox.style.width = `${ width }px`;
	            }
	            if ( delta[ 1 ] ) {
	              cropBox.style.height = `${ height }px`;
	            }
	            cropRef.current = [ tx, ty, width, height ];
	          } }
					onResizeEnd={ () => {
	          	cropBox.style.transform = '';
	          	croppedImage.style.transform = '';
	          	cropBox.style.width = '';
	          	cropBox.style.height = '';
	            const [ tx, ty, width, height ] = cropRef.current;
	            cropRef.current = [ 0, 0 ];
	            const { offsetX, offsetY, width: imgWidth, height: imgHeight } = imgProps;
	            const scale = Math.min( imgWidth / width, imgHeight / height ) * 100;
	            const focalX = getFocalFromOffset( width, imgWidth, offsetX + tx );
	            const focalY = getFocalFromOffset( height, imgHeight, offsetY + ty );
	            setPropertiesById( id, {
	            	x: elementProps.x + tx,
	            	y: elementProps.y + ty,
	            	keepRatio: false,
	              width,
	              height,
	              scale,
	            	focalX,
	            	focalY,
	            } );
	          } }

					snappable={ true }
	          // todo@: it looks like resizing bounds are not supported.
					verticalGuidelines={ [
	            elementProps.x - imgProps.offsetX,
	            elementProps.x - imgProps.offsetX + imgProps.width,
	          ] }
					horizontalGuidelines={ [
	            elementProps.y - imgProps.offsetY,
	            elementProps.y - imgProps.offsetY + imgProps.height,
					] }
	        />
			) }

			{ /* Draggable moveable for panning */ }
			<Movable
				ref={ panMoveableRef }
				targets={ croppedImage }

				origin={ true }

				edge={ false }
				resizable={ false }
				scalable={ false }
				rotatable={ false }
				warpable={ false }
				pinchable={ false }

				draggable={ true }
				throttleDrag={ 0 }
				onDrag={ ( { dist } ) => {
					translateRef.current = dist;
					updatePan();
				} }
				onDragEnd={ () => {
					const [ tx, ty ] = translateRef.current;
					translateRef.current = [ 0, 0 ];
					const { width, height } = elementProps;
					const { offsetX, offsetY, width: imgWidth, height: imgHeight } = imgProps;
					const focalX = getFocalFromOffset( width, imgWidth, offsetX - tx );
					const focalY = getFocalFromOffset( height, imgHeight, offsetY - ty );
					setPropertiesById( id, { focalX, focalY } );
					updatePan();
				} }

				// Snappable
				snappable={ true }
				snapCenter={ true }
				bounds={ {
					left: elementProps.x + elementProps.width - imgProps.width,
					top: elementProps.y + elementProps.height - imgProps.height,
					right: elementProps.x + imgProps.width,
					bottom: elementProps.y + imgProps.height,
				} }
				verticalGuidelines={ [
					elementProps.x,
					elementProps.x + elementProps.width / 2,
					elementProps.x + elementProps.width,
				] }
				horizontalGuidelines={ [
					elementProps.y,
					elementProps.y + elementProps.height / 2,
					elementProps.y + elementProps.height,
				] }
			/>

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
