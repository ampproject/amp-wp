/**
 * Internal dependencies
 */
import {
	BLOCKS_WITH_TEXT_SETTINGS,
	REVERSE_WIDTH_CALCULATIONS,
	REVERSE_HEIGHT_CALCULATIONS,
} from '../../constants';
import { getPixelsFromPercentage } from '../../helpers';

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

/**
 * Get block positioning after resizing, not considering the rotation.
 *
 * @param {string} direction Resizing direction.
 * @param {string} blockElementLeft Original left position before resizing.
 * @param {string} blockElementTop Original top position before resizing.
 * @param {number} deltaW Width change with resizing.
 * @param {number} deltaH Height change with resizing.
 * @return {{top: number, left: number}} Top and left positioning after resizing, not considering the rotation.
 */
export const getResizedBlockPosition = ( direction, blockElementLeft, blockElementTop, deltaW, deltaH ) => {
	const baseLeftInPixels = getPixelsFromPercentage( 'x', parseFloat( blockElementLeft ) );
	const baseTopInPixels = getPixelsFromPercentage( 'y', parseFloat( blockElementTop ) );
	switch ( direction ) {
		case 'topRight':
			return {
				left: baseLeftInPixels,
				top: baseTopInPixels - deltaH,
			};
		case 'bottomLeft':
			return {
				left: baseLeftInPixels - deltaW,
				top: baseTopInPixels,
			};
		case 'left':
		case 'topLeft':
		case 'top':
			return {
				left: baseLeftInPixels - deltaW,
				top: baseTopInPixels - deltaH,
			};
		default:
			return {
				left: baseLeftInPixels,
				top: baseTopInPixels,
			};
	}
};

/**
 * Get block position after resizing, considering the rotation.
 *
 * @param {string} direction Resizing direction.
 * @param {Object} originalPosition Original block position, considering the rotation.
 * @param {Object} diff Block position difference after resizing.
 * @return {{top: number, left: number}} Top and left params in pixels.
 */
export const getUpdatedBlockPosition = ( direction, originalPosition, diff ) => {
	switch ( direction ) {
		case 'topRight':
			return {
				left: originalPosition.left - diff.left,
				top: originalPosition.top - diff.top,
			};
		case 'bottomLeft':
			return {
				left: originalPosition.left + diff.left,
				top: originalPosition.top + diff.top,
			};
		case 'left':
		case 'topLeft':
		case 'top':
			return {
				left: originalPosition.left + diff.left,
				top: originalPosition.top - diff.top,
			};
		default:
			return {
				left: originalPosition.left - diff.left,
				top: originalPosition.top + diff.top,
			};
	}
};

/**
 * Get block's left and top position based on width, height, and radian.
 *
 * @param {number} width Width.
 * @param {number} height Height.
 * @param {number} radian Radian.
 * @param {string} direction Resizing direction.
 * @return {{top: number, left: number}} Top and left positioning.
 */
export const getBlockPositioning = ( width, height, radian, direction ) => {
	// The center point of the block.
	const x = -width / 2;
	const y = height / 2;

	let rotatedX, rotatedY;
	// Get the center point of the rotated block.
	if ( 'topRight' === direction || 'bottomLeft' === direction ) {
		rotatedX = ( y * -Math.sin( radian ) ) + ( x * Math.cos( radian ) );
		rotatedY = ( y * Math.cos( radian ) ) - ( x * -Math.sin( radian ) );
	} else {
		rotatedX = ( y * Math.sin( radian ) ) + ( x * Math.cos( radian ) );
		rotatedY = ( y * Math.cos( radian ) ) - ( x * Math.sin( radian ) );
	}

	return {
		left: rotatedX - x,
		top: rotatedY - y,
	};
};
