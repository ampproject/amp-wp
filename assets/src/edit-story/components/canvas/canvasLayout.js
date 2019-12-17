/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import useCanvas from './useCanvas';
import Page from './page';
import Meta from './meta';
import Carousel from './carousel';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;

	display: grid;
	grid:
    ".   meta              .       " 1fr
    ".   page              .       " ${ PAGE_HEIGHT }px
    ".          .          .       " 1fr
    "carousel   carousel   carousel" auto
    / 1fr ${ PAGE_WIDTH }px 1fr;
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	height: 100%;
	width: 100%;
`;

function CanvasLayout() {
	const { state: { backgroundClickHandler } } = useCanvas();
	return (
		<Background onClick={ backgroundClickHandler }>
			<Area area="page">
				<Page />
			</Area>
			<Area area="meta">
				<Meta />
			</Area>
			<Area area="carousel">
				<Carousel />
			</Area>
		</Background>
	);
}

export default CanvasLayout;
