
/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import Header from '../../components/header';
import Inspector from '../../components/inspector';
import Library from '../../components/library';
import CanvasProvider from '../../components/canvas/canvasProvider';
import Page from '../../components/canvas/page';
import PageMeta from '../../components/pageMeta';
import PageCarousel from '../../components/pageCarousel';
import SelectionCanvas from '../../components/canvas/selectionCanvas';
import { LIBRARY_WIDTH, INSPECTOR_WIDTH, HEADER_HEIGHT, PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';

// Canvas is below all other overlaying elements such as library and inspector.
export const DEFAULT_Z_INDEX = 20;
export const CANVAS_Z_INDEX = 10;

const Editor = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	position: absolute;
	left: -20px;
	top: 0;
	right: 0;
	bottom: 0;
	min-height: calc(100vh - 32px);

  display: grid;
  grid:
    "lib  header  header" ${ HEADER_HEIGHT }px
    "lib  main  insp" 1fr
    / ${ LIBRARY_WIDTH }px 1fr ${ INSPECTOR_WIDTH }px;
`;

const Area = styled.div`
  grid-area: ${ ( { area } ) => area };
  position: relative;
  z-index: ${ ( { area } ) => area === 'main' ? CANVAS_Z_INDEX : DEFAULT_Z_INDEX };
`;

const MainLayout = styled.div`
  width: 100%;
  height: 100%;

  display: grid;
  grid:
    ".   meta       ." 1fr
    ".   page       ." ${ PAGE_HEIGHT }px
    ".   carrousel  ." 1fr
    / 1fr ${ PAGE_WIDTH }px 1fr;
`;

const CanvasArea = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: ${ CANVAS_Z_INDEX };
`;

function Layout() {
	return (
		<Editor>
			<Area area="header">
				<Header />
			</Area>
			<Area area="lib">
				<Library />
			</Area>
			<Area area="main">
				<MainLayout>
					<CanvasArea>
						<CanvasProvider>
							<SelectionCanvas>
								<MainLayout>
									<Area area="page">
										<Page />
									</Area>
								</MainLayout>
							</SelectionCanvas>
						</CanvasProvider>
					</CanvasArea>
					<Area area="meta">
						<PageMeta />
					</Area>
					<Area area="carrousel">
						<PageCarousel />
					</Area>
				</MainLayout>
			</Area>
			<Area area="insp">
				<Inspector />
			</Area>
		</Editor>
	);
}

export default Layout;
