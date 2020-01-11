/**
 * External dependencies
 */
import styled, { css } from 'styled-components';
import PropTypes from 'prop-types';
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ElementFillContent } from '../elements/shared';

const MaskIds = {
	HEART: 'heart',
	STAR: 'star',
};

const CLIP_PATHS = {
	// @todo: This is a very bad heart.
	[ MaskIds.HEART ]: 'M 0.5,1 C 0.5,1,0,0.7,0,0.3 A 0.25,0.25,1,1,1,0.5,0.3 A 0.25,0.25,1,1,1,1,0.3 C 1,0.7,0.5,1,0.5,1 Z',
	// @todo: This is a horrible star.
	[ MaskIds.STAR ]: 'M .5,0 L .8,1 L 0,.4 L 1,.4 L .2,1 Z',
};

export const MASKS = [
	{
		type: MaskIds.HEART,
		name: 'Heart',
		path: CLIP_PATHS[ MaskIds.HEART ],
	},
	{
		type: MaskIds.STAR,
		name: 'Star',
		path: CLIP_PATHS[ MaskIds.STAR ],
	},
];

// Pointer events have to be restored to ensure that selection works, but
// limited to the mask.
const MaskCss = css`
  ${ ElementFillContent }
  pointer-events: initial;
`;

const NoMask = styled.div`
  ${ MaskCss }
`;

const ClipPathMask = styled.div`
  ${ MaskCss }
  clip-path: url(#${ ( { maskId } ) => maskId });
`;

export function WithElementMask( { children, ...elementProps } ) {
	const mask = getElementMaskProperties( elementProps );
	return (
		<WithtMask { ...mask }>
			{ children }
		</WithtMask>
	);
}

WithElementMask.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

function WithtMask( { children, type, ...mask } ) {
	const maskId = useMemo( () => type + '-' + uuid(), [ type ] );
	if ( type ) {
		// @todo: Chrome cannot do inline clip-path using data: URLs.
		// See https://bugs.chromium.org/p/chromium/issues/detail?id=1041024.
		return (
			<ClipPathMask maskId={ maskId } { ...mask }>
				<svg width={ 0 } height={ 0 }>
					<defs>
						<clipPath id={ maskId } clipPathUnits="objectBoundingBox">
							<path d={ CLIP_PATHS[ type ] } />
						</clipPath>
					</defs>
				</svg>
				{ children }
			</ClipPathMask>
		);
	}
	return (
		<NoMask>
			{ children }
		</NoMask>
	);
}

WithtMask.propTypes = {
	type: PropTypes.string.isRequired,
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

function getElementMaskProperties( { type, mask, ...rest } ) {
	if ( mask ) {
		return mask;
	}
	return getDefaultElementMaskProperties( { type, ...rest } );
}

function getDefaultElementMaskProperties( { } ) {
	// @todo: mask-based shapes (square, circle, etc) automatically assume masks.
	return null;
}
