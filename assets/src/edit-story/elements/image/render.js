
/**
 * Internal dependencies
 */
import { getBox } from '../shared';
import { getImgProps } from './util';

function ImageRender( context, { src, origRatio, width, height, x, y, scale, focalX, focalY, rotationAngle, isFullbleed } ) {
	return loadImage( src ).then( ( image ) => {
		const { naturalWidth } = image;
		const elementProps = getBox( { x, y, width, height, rotationAngle, isFullbleed } );
		const imgProps = getImgProps( elementProps.width, elementProps.height, scale, focalX, focalY, origRatio );
		const naturalScale = naturalWidth / imgProps.width;
		const sx = imgProps.offsetX * naturalScale;
		const sy = imgProps.offsetY * naturalScale;
		const sw = elementProps.width * naturalScale;
		const sh = elementProps.height * naturalScale;
		context.drawImage(
			image,
			sx, sy, sw, sh,
			elementProps.x, elementProps.y, elementProps.width, elementProps.height,
		);
	} );
}

function loadImage( src ) {
	return new Promise( ( resolve, reject ) => {
		const image = new window.Image();
		image.onload = () => resolve( image );
		image.onerror = ( reason ) => reject( reason );
		image.src = src;
	} );
}

export default ImageRender;
