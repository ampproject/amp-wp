/**
 * External dependencies
 */
import { css } from 'styled-components';

export const ImageWithScale = css`
	width: ${ ( { width } ) => `${ width }px` };
	height: ${ ( { height } ) => `${ height }px` };
	left: ${ ( { offsetX } ) => `${ -offsetX }px` };
	top: ${ ( { offsetY } ) => `${ -offsetY }px` };
`;

export function getImgProps( width, height, scale, focalX, focalY, imgRatio ) {
	const ratio = width / height;
	scale = Math.max( scale || 100, 100 );
	focalX = typeof focalX === 'number' ? focalX : 50;
	focalY = typeof focalY === 'number' ? focalY : 50;
	const imgWidth = ( imgRatio <= ratio ? width : height * imgRatio ) * scale * 0.01;
	const imgHeight = ( imgRatio <= ratio ? width / imgRatio : height ) * scale * 0.01;
	const offsetX = Math.max( 0, Math.min( ( imgWidth * focalX * 0.01 ) - ( width * 0.5 ), imgWidth - width ) );
	const offsetY = Math.max( 0, Math.min( ( imgHeight * focalY * 0.01 ) - ( height * 0.5 ), imgHeight - height ) );
	return {
		width: imgWidth,
		height: imgHeight,
		offsetX,
		offsetY,
		scale,
		focalX,
		focalY,
	};
}

export function getFocalFromOffset( side, imgSide, offset) {
	return (offset + (side * 0.5)) / imgSide * 100;
}
