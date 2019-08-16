/**
 * Internal dependencies
 */
import { BLOCKS_WITH_TEXT_SETTINGS } from '../../constants';

export const REVERSE_WIDTH_CALCULATIONS = [
	'left',
	'bottomLeft',
	'topLeft',
];

export const REVERSE_HEIGHT_CALCULATIONS = [
	'top',
	'topRight',
	'topLeft',
];

/**
 * Get the distance between two points based on pythagorean.
 *
 * @param {number} deltaX Difference between X coordinates.
 * @param {number} deltaY Difference between Y coordinates.
 * @return {number} Difference between the two points.
 */
const getDelta = ( deltaX, deltaY ) => Math.sqrt( Math.pow( deltaX, 2 ) + Math.pow( deltaY, 2 ) );

/**
 * Converts degrees to radian.
 *
 * @param {number} angle Angle.
 * @return {number} Radian.
 */
export const getRadianFromDeg = ( angle ) => angle * Math.PI / 180;

/**
 * Gets width and height delta values based on the original coordinates, rotation angle and mouse event.
 *
 * @param {Object} event MouseEvent.
 * @param {number} angle Rotation angle.
 * @param {number} lastSeenX Starting X coordinate.
 * @param {number} lastSeenY Starting Y coordinate.
 * @param {string} direction Direction of resizing.
 * @return {Object} Width and height values.
 */
export const getResizedWidthAndHeight = ( event, angle, lastSeenX, lastSeenY, direction ) => {
	const deltaY = event.clientY - lastSeenY;
	const deltaX = event.clientX - lastSeenX;
	const deltaL = getDelta( deltaX, deltaY );

	// Get the angle between the two points.
	const alpha = Math.atan2( deltaY, deltaX );
	// Get the difference with rotation angle.
	const beta = alpha - getRadianFromDeg( angle );

	const resizedHorizontally = direction.toLowerCase().includes( 'left' ) || direction.toLowerCase().includes( 'right' );
	const resizedVertically = direction.toLowerCase().includes( 'top' ) || direction.toLowerCase().includes( 'bottom' );
	const deltaW = resizedHorizontally ? deltaL * Math.cos( beta ) : 0;
	const deltaH = resizedVertically ? deltaL * Math.sin( beta ) : 0;

	// When using left or top handles the size of the block is changing reversely to right and bottom.
	return {
		deltaW: REVERSE_WIDTH_CALCULATIONS.includes( direction ) ? -deltaW : deltaW,
		deltaH: REVERSE_HEIGHT_CALCULATIONS.includes( direction ) ? -deltaH : deltaH,
	};
};

/**
 * Returns the block's inner text element.
 *
 * @param {string} blockName Block name.
 * @param {HTMLElement} blockElement The block's `.wp-block` element.
 * @return {?HTMLElement} Inner element if found, otherwise null.
 */
export const getBlockTextElement = ( blockName, blockElement ) => {
	const isBlockWithText = BLOCKS_WITH_TEXT_SETTINGS.includes( blockName ) || 'core/code' === blockName;

	if ( ! isBlockWithText ) {
		return null;
	}

	switch ( blockName ) {
		case 'amp/amp-story-text':
			return blockElement.querySelector( '.block-editor-rich-text__editable.editor-rich-text__editable' );
		case 'amp/amp-story-post-title':
			return blockElement.querySelector( '.wp-block-amp-amp-story-post-title' );
		case 'amp/amp-story-post-author':
			return blockElement.querySelector( '.wp-block-amp-amp-story-post-author' );
		case 'amp/amp-story-post-date':
			return blockElement.querySelector( '.wp-block-amp-amp-story-post-date' );
		case 'core/code':
			return blockElement.querySelector( '.wp-block-code' );
		default:
			return null;
	}
};
