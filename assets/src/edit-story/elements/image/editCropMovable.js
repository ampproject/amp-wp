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

function EditCropMovable( {
	setProperties, cropBox, croppedImage,
	x, y,
	offsetX, offsetY, imgWidth, imgHeight,
} ) {
	const moveableRef = useRef();
	const cropRef = useRef( [ 0, 0, 0, 0 ] );

	// Refresh moveables to ensure that the selection rect is always correct.
	useEffect( () => {
		moveableRef.current.updateRect();
	} );

	return (
		<Movable
			ref={ moveableRef }
			className="crop-movable"
			targets={ cropBox }

			origin={ false }
			resizable={ true }
			onResize={ ( { width: resizeWidth, height: resizeHeight, delta, drag } ) => {
				const [ tx, ty ] = [ drag.beforeTranslate[ 0 ], drag.beforeTranslate[ 1 ] ];
				cropBox.style.transform = `translate(${ tx }px, ${ ty }px)`;
				croppedImage.style.transform = `translate(${ -tx }px, ${ -ty }px)`;
				if ( delta[ 0 ] ) {
					cropBox.style.width = `${ resizeWidth }px`;
				}
				if ( delta[ 1 ] ) {
					cropBox.style.height = `${ resizeHeight }px`;
				}
				cropRef.current = [ tx, ty, resizeWidth, resizeHeight ];
			} }
			onResizeEnd={ () => {
				cropBox.style.transform = '';
				croppedImage.style.transform = '';
				cropBox.style.width = '';
				cropBox.style.height = '';
				const [ tx, ty, resizeWidth, resizeHeight ] = cropRef.current;
				cropRef.current = [ 0, 0, 0, 0 ];
				if ( resizeWidth === 0 || resizeHeight === 0 ) {
					return;
				}
				const resizeScale = Math.min( imgWidth / resizeWidth, imgHeight / resizeHeight ) * 100;
				const resizeFocalX = getFocalFromOffset( resizeWidth, imgWidth, offsetX + tx );
				const resizeFocalY = getFocalFromOffset( resizeHeight, imgHeight, offsetY + ty );
				setProperties( {
					x: x + tx,
					y: y + ty,
					width: resizeWidth,
					height: resizeHeight,
					scale: resizeScale,
					focalX: resizeFocalX,
					focalY: resizeFocalY,
				} );
			} }

			snappable={ true }
			// todo@: it looks like resizing bounds are not supported.
			verticalGuidelines={ [
				x - offsetX,
				x - offsetX + imgWidth,
			] }
			horizontalGuidelines={ [
				y - offsetY,
				y - offsetY + imgHeight,
			] }
		/>
	);
}

EditCropMovable.propTypes = {
	setProperties: PropTypes.func.isRequired,
	cropBox: PropTypes.object.isRequired,
	croppedImage: PropTypes.object.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	offsetX: PropTypes.number.isRequired,
	offsetY: PropTypes.number.isRequired,
	imgWidth: PropTypes.number.isRequired,
	imgHeight: PropTypes.number.isRequired,
};

export default EditCropMovable;
