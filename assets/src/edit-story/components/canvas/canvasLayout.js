/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { CENTRAL_RIGHT_PADDING, LEFT_NAV_WIDTH, PAGE_WIDTH, PAGE_HEIGHT, PAGE_NAV_PADDING } from '../../constants';
import Page from './page';
import PageMenu from './pagemenu';
import PageNav from './pagenav';
import Carrousel from './carrousel';
import SelectionCanvas from './selectionCanvas';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;

	display: grid;
	grid:
    ".    .      .          . .        ." 1fr
    ".    prev   page       . next     ." ${ PAGE_HEIGHT }px
    ".    .      menu       . .        ." 48px
    ".    .      carrousel  . .        ." 1fr
    / 1fr ${ LEFT_NAV_WIDTH }px ${ PAGE_WIDTH }px ${ PAGE_NAV_PADDING }px 1fr ${ CENTRAL_RIGHT_PADDING }px;
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	height: 100%;
	width: 100%;
`;

function CanvasLayout() {
	return (
		<SelectionCanvas>
			<Background>
				<Area area="page">
					<Page />
				</Area>
				<Area area="menu">
					<PageMenu />
				</Area>
				<Area area="prev">
					<PageNav isNext={ false } />
				</Area>
				<Area area="next">
					<PageNav />
				</Area>
				<Area area="carrousel">
					<Carrousel />
				</Area>
			</Background>
		</SelectionCanvas>
	);
}

export default CanvasLayout;
