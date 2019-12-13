/**
 * External dependencies
 */
import { css } from 'styled-components';

export const ImageWithScale = css`
	width: ${ ( { scale } ) => `${ scale }%` };
	height: auto;
	left: ${ ( { offsetX } ) => `${ -offsetX }%` };
	top: ${ ( { offsetY } ) => `${ -offsetY }%` };
`;

function getImgScale( origRatio, actualRatio ) {
	return actualRatio > origRatio ? 100 : 100 * origRatio / actualRatio;
}

function getImgOffsetX( scale ) {
	return scale === 100 ? 0 : ( scale - 100 ) / 2;
}

function getImgOffsetY( scale, origRatio, actualRatio ) {
	return ( ( scale * actualRatio / origRatio ) - 100 ) / 2;
}

export function getImgProps( scale, offsetX, offsetY, origRatio, actualRatio ) {
	const imgScale = typeof scale === 'number' ? scale : getImgScale( origRatio, actualRatio );
	const imgOffsetX = typeof offsetX === 'number' ? offsetX : getImgOffsetX( imgScale );
	const imgOffsetY = typeof offsetY === 'number' ? offsetY : getImgOffsetY( imgScale, origRatio, actualRatio );
	return {
		scale: imgScale,
		offsetX: imgOffsetX,
		offsetY: imgOffsetY,
	};
}
