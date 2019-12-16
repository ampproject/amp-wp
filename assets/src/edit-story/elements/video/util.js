/**
 * External dependencies
 */
import { css } from 'styled-components';

export const VideoWithScale = css`
	width: ${ ( { scale } ) => `${ scale }%` };
	height: auto;
	left: ${ ( { offsetX } ) => `${ -offsetX }%` };
	top: ${ ( { offsetY } ) => `${ -offsetY }%` };
`;

function getVideoScale( origRatio, actualRatio ) {
	return actualRatio > origRatio ? 100 : 100 * origRatio / actualRatio;
}

function getVideoOffsetX( scale ) {
	return scale === 100 ? 0 : ( scale - 100 ) / 2;
}

function getVideoOffsetY( scale, origRatio, actualRatio ) {
	return ( ( scale * actualRatio / origRatio ) - 100 ) / 2;
}

export function getVideoProps( scale, offsetX, offsetY, origRatio, actualRatio ) {
	const imgScale = typeof scale === 'number' ? scale : getVideoScale( origRatio, actualRatio );
	const imgOffsetX = typeof offsetX === 'number' ? offsetX : getVideoOffsetX( imgScale );
	const imgOffsetY = typeof offsetY === 'number' ? offsetY : getVideoOffsetY( imgScale, origRatio, actualRatio );
	return {
		scale: imgScale,
		offsetX: imgOffsetX,
		offsetY: imgOffsetY,
	};
}
