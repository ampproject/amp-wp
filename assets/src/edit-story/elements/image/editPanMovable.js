/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Movable from '../../components/movable';
import { getFocalFromOffset } from './util';

function EditPanMovable( {
	setProperties, fullImage, croppedImage,
	x, y, width, height, rotationAngle,
	offsetX, offsetY, imgWidth, imgHeight,
} ) {
	const moveableRef = useRef();
	const translateRef = useRef( [ 0, 0 ] );

	const update = () => {
		const [ tx, ty ] = translateRef.current;
		fullImage.style.transform = `translate(${ tx }px, ${ ty }px)`;
		croppedImage.style.transform = `translate(${ tx }px, ${ ty }px)`;
	};

	// Refresh moveables to ensure that the selection rect is always correct.
	useEffect( () => {
		moveableRef.current.updateRect();
	} );

	return (
		<Movable
			ref={ moveableRef }
			targets={ croppedImage }

			origin={ true }
			draggable={ true }
			throttleDrag={ 0 }
			onDrag={ ( { dist } ) => {
				translateRef.current = dist;
				update();
			} }
			onDragEnd={ () => {
				const [ tx, ty ] = translateRef.current;
				translateRef.current = [ 0, 0 ];
				setProperties( {
					focalX: getFocalFromOffset( width, imgWidth, offsetX - tx ),
					focalY: getFocalFromOffset( height, imgHeight, offsetY - ty ),
				} );
				update();
			} }

			// Snappable
			snappable={ true }
			snapCenter={ true }
			// todo@: Moveable defines bounds and guidelines as the vertical and
			// horizontal lines and doesn't work well with `rotationAngle > 0` for
			// cropping/panning. It's possible to define a larger bounds using
			// the expansion radius, but the UX is very poor for a rotated shape.
			bounds={ rotationAngle === 0 ? {
				left: x + width - imgWidth,
				top: y + height - imgHeight,
				right: x + imgWidth,
				bottom: y + imgHeight,
			} : {} }
			verticalGuidelines={ rotationAngle === 0 ? [
				x,
				x + ( width / 2 ),
				x + width,
			] : [ x + ( width / 2 ) ] }
			horizontalGuidelines={ rotationAngle === 0 ? [
				y,
				y + ( height / 2 ),
				y + height,
			] : [ y + ( height / 2 ) ] }
		/>
	);
}

EditPanMovable.propTypes = {
	setProperties: PropTypes.func.isRequired,
	fullImage: PropTypes.object.isRequired,
	croppedImage: PropTypes.object.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	rotationAngle: PropTypes.number.isRequired,
	offsetX: PropTypes.number.isRequired,
	offsetY: PropTypes.number.isRequired,
	imgWidth: PropTypes.number.isRequired,
	imgHeight: PropTypes.number.isRequired,
};

export default EditPanMovable;
