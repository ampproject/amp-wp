
/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../constants';

export function dataUnit( v ) {
	return Number( v.toFixed( 0 ) );
}

export function editorUnit( v ) {
	return Number( v.toFixed( 5 ) );
}

export function dataToEditorX( x, pageWidth ) {
	return editorUnit( x * pageWidth / PAGE_WIDTH );
}

export function dataToEditorY( y, pageHeight ) {
	return editorUnit( y * pageHeight / PAGE_HEIGHT );
}

export function editorToDataX( x, pageWidth ) {
	return dataUnit( x * PAGE_WIDTH / pageWidth );
}

export function editorToDataY( y, pageHeight ) {
	return dataUnit( y * PAGE_HEIGHT / pageHeight );
}

export function getBox( { x, y, width, height, rotationAngle, isFullbleed }, pageWidth, pageHeight ) {
	return {
		x: dataToEditorX( isFullbleed ? 0 : x, pageWidth ),
		y: dataToEditorY( isFullbleed ? 0 : y, pageHeight ),
		width: dataToEditorX( isFullbleed ? PAGE_WIDTH : width, pageWidth ),
		height: dataToEditorY( isFullbleed ? PAGE_HEIGHT : height, pageHeight ),
		rotationAngle: isFullbleed ? 0 : rotationAngle,
	};
}
