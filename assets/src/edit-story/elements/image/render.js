
/**
 * Internal dependencies
 */
import { getImgProps } from './util';

function ImageRender( context, { src, origRatio, width, height, scale, focalX, focalY } ) {
	return loadImage( src ).then( ( image ) => {
		const { naturalWidth } = image;
		const imgProps = getImgProps( width, height, scale, focalX, focalY, origRatio );
		const naturalScale = naturalWidth / imgProps.width;
		const sx = imgProps.offsetX * naturalScale;
		const sy = imgProps.offsetY * naturalScale;
		const sw = width * naturalScale;
		const sh = height * naturalScale;
		context.drawImage(
			image,
			sx, sy, sw, sh,
			0, 0, width, height,
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
