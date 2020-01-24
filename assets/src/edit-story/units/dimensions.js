
/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../constants';

/**
 * Rounds the pixel value to the max allowed precision in the "data" space.
 *
 * @param {number} v The value to be rounded.
 * @return {number} The value rounded to the "data" space precision.
 */
export function dataPixels( v ) {
	return Number( v.toFixed( 0 ) );
}

/**
 * Rounds the pixel value to the max allowed precision in the "editor" space.
 *
 * @param {number} v The value to be rounded.
 * @return {number} The value rounded to the "editor" space precision.
 */
export function editorPixels( v ) {
	return Number( v.toFixed( 5 ) );
}

/**
 * Converts a "data" pixel value to the value in the "editor" space along
 * the horizontal (X) dimension.
 *
 * @param {number} x The value to be converted.
 * @param {number} pageWidth The basis value for the page's width in the "editor" space.
 * @return {number} The value in the "editor" space.
 */
export function dataToEditorX( x, pageWidth ) {
	return editorPixels( x * pageWidth / PAGE_WIDTH );
}

/**
 * Converts a "data" pixel value to the value in the "editor" space along
 * the vertical (Y) dimension.
 *
 * @param {number} y The value to be converted.
 * @param {number} pageHeight The basis value for the page's height in the "editor" space.
 * @return {number} The value in the "editor" space.
 */
export function dataToEditorY( y, pageHeight ) {
	return editorPixels( y * pageHeight / PAGE_HEIGHT );
}

/**
 * Converts a "editor" pixel value to the value in the "data" space along
 * the horizontal (X) dimension.
 *
 * @param {number} x The value to be converted.
 * @param {number} pageWidth The basis value for the page's width in the "editor" space.
 * @return {number} The value in the "data" space.
 */
export function editorToDataX( x, pageWidth ) {
	return dataPixels( x * PAGE_WIDTH / pageWidth );
}

/**
 * Converts a "editor" pixel value to the value in the "data" space along
 * the vertical (Y) dimension.
 *
 * @param {number} y The value to be converted.
 * @param {number} pageHeight The basis value for the page's height in the "editor" space.
 * @return {number} The value in the "data" space.
 */
export function editorToDataY( y, pageHeight ) {
	return dataPixels( y * PAGE_HEIGHT / pageHeight );
}

/**
 * Converts the element's position, width, and rotation) to the "box" in the
 * "editor" coordinate space.
 *
 * @param {{x:number, y:number, width:number, height:number, rotationAngle:number, isFullbleed:boolean}} element The
 * element's position, width, and rotation. See `StoryPropTypes.element`.
 * @param {number} pageWidth The basis value for the page's width in the "editor" space.
 * @param {number} pageHeight The basis value for the page's height in the "editor" space.
 * @return {{x:number, y:number, width:number, height:number, rotationAngle:number}} The
 * "box" in the editor space.
 */
export function getBox( { x, y, width, height, rotationAngle, isFullbleed }, pageWidth, pageHeight ) {
	return {
		x: dataToEditorX( isFullbleed ? 0 : x, pageWidth ),
		y: dataToEditorY( isFullbleed ? 0 : y, pageHeight ),
		width: dataToEditorX( isFullbleed ? PAGE_WIDTH : width, pageWidth ),
		height: dataToEditorY( isFullbleed ? PAGE_HEIGHT : height, pageHeight ),
		rotationAngle: isFullbleed ? 0 : rotationAngle,
	};
}
