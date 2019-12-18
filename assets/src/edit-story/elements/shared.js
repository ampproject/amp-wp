/**
 * External dependencies
 */
import { css } from 'styled-components';
/**
 * Internal dependencies
 */
import { PAGE_HEIGHT, PAGE_WIDTH } from '../constants';

export const ElementWithPosition = css`
	position: absolute;
	z-index: 1;
	left: ${ ( { x } ) => `${ x }px` };
	top: ${ ( { y } ) => `${ y }px` };
`;

export const ElementWithSize = css`
	width: ${ ( { width } ) => `${ width }px` };
	height: ${ ( { height } ) => `${ height }px` };
`;

export const ElementWithRotation = css`
	transform: ${ ( { rotationAngle } ) => `rotate(${ rotationAngle }deg)` };
`;

export const ElementWithBackgroundColor = css`
	background-color: ${ ( { backgroundColor } ) => backgroundColor };
`;

export const ElementWithFontColor = css`
	color: ${ ( { color } ) => color };
`;

export const ElementWithFont = css`
	line-height: 1.3;
	white-space: pre-wrap;
	font-family: ${ ( { fontFamily } ) => fontFamily };
	font-style: ${ ( { fontStyle } ) => fontStyle };
	font-size: ${ ( { fontSize } ) => fontSize };
	font-weight: ${ ( { fontWeight } ) => fontWeight };
`;

export function getBox( { x, y, width, height, rotationAngle, isFullbleed } ) {
	return {
		x: isFullbleed ? 0 : x,
		y: isFullbleed ? 0 : y,
		width: isFullbleed ? PAGE_WIDTH : width,
		height: isFullbleed ? PAGE_HEIGHT : height,
		rotationAngle: isFullbleed ? 0 : rotationAngle,
	};
}
