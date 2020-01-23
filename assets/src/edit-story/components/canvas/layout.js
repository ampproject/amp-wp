/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PAGE_NAV_WIDTH, PAGE_WIDTH, PAGE_HEIGHT, HEADER_HEIGHT } from '../../constants';
import PointerEventsCss from '../../utils/pointerEventsCss';
import useResizeEffect from '../../utils/useResizeEffect';
import useCanvas from './useCanvas';

/**
 * @file See https://user-images.githubusercontent.com/726049/72654503-bfffe780-3944-11ea-912c-fc54d68b6100.png
 * for the layering details.
 */

const MENU_HEIGHT = 48;
const MIN_CAROUSEL_HEIGHT = 65;
const ALLOWED_PAGE_SIZES = [
	[ PAGE_WIDTH, PAGE_HEIGHT ],
	[ 268, 476 ],
];

// @todo: the menu and carousel heights are not correct until we make a var-size
// page.
const Layer = styled.div`
  ${ PointerEventsCss }

  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  display: grid;
  grid:
    "head      head      head      head      head    " ${ HEADER_HEIGHT }px
    ".         .         .         .         .       " 1fr
    ".         prev      page      next      .       " var(--page-height-px)
    ".         .         menu      .         .       " ${ MENU_HEIGHT }px
    ".         .         .         .         .       " 1fr
    "carousel  carousel  carousel  carousel  carousel" ${ MIN_CAROUSEL_HEIGHT }px
    / 1fr ${ PAGE_NAV_WIDTH }px var(--page-width-px) ${ PAGE_NAV_WIDTH }px 1fr;
`;

const Area = styled.div`
  ${ PointerEventsCss }

  grid-area: ${ ( { area } ) => area };
  overflow: ${ ( { overflow } ) => overflow ? 'visible' : 'hidden' };
  position: relative;
  width: 100%;
  height: 100%;
`;

// Page area is not `overflow:hidden` by default to allow different clipping
// mechanisms.
const PageArea = styled( Area ).attrs( { area: 'page', overflow: true } )``;

const HeadArea = styled( Area ).attrs( { area: 'head', overflow: false } )``;

const MenuArea = styled( Area ).attrs( { area: 'menu', overflow: false } )``;

const NavArea = styled( Area ).attrs( { overflow: false } )`
  display: flex;
  align-items: center;
  justify-content: center;
`;

const NavPrevArea = styled( NavArea ).attrs( { area: 'prev' } )``;

const NavNextArea = styled( NavArea ).attrs( { area: 'next' } )``;

const CarouselArea = styled( Area ).attrs( { area: 'carousel', overflow: false } )``;

/**
 * @param {!{current: ?Element}} containerRef
 */
function useLayoutParams( containerRef ) {
	const { actions: { setPageSize } } = useCanvas();

	useResizeEffect( containerRef, ( { width, height } ) => {
		// See Layer's `grid` CSS above. Per the layout, the maximum available
		// space for the page is:
		const maxWidth = width - ( PAGE_NAV_WIDTH * 2 );
		const maxHeight = height - HEADER_HEIGHT - MENU_HEIGHT - MIN_CAROUSEL_HEIGHT;

		// Find the first size that fits within the [maxWidth, maxHeight].
		let bestSize = ALLOWED_PAGE_SIZES[ ALLOWED_PAGE_SIZES.length - 1 ];
		for ( let i = 0; i < ALLOWED_PAGE_SIZES.length; i++ ) {
			const size = ALLOWED_PAGE_SIZES[ i ];
			if ( size[ 0 ] <= maxWidth && size[ 1 ] <= maxHeight ) {
				bestSize = size;
				break;
			}
		}
		setPageSize( { width: bestSize[ 0 ], height: bestSize[ 1 ] } );
	} );
}

function useLayoutParamsCssVars() {
	const { state: { pageSize } } = useCanvas();
	return {
		'--page-width-px': `${ pageSize.width }px`,
		'--page-height-px': `${ pageSize.height }px`,
	};
}

export {
	Layer,
	PageArea,
	HeadArea,
	MenuArea,
	NavPrevArea,
	NavNextArea,
	CarouselArea,
	useLayoutParams,
	useLayoutParamsCssVars,
};
