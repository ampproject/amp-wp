
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
import Canvas from '../../components/canvas';
import DropzoneProvider from '../../components/dropzone/dropzoneProvider';
import { LIBRARY_WIDTH, INSPECTOR_WIDTH, HEADER_HEIGHT } from '../../constants';

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
    "lib  head  head" ${ HEADER_HEIGHT }px
    "lib  canv  insp" 1fr
    / ${ LIBRARY_WIDTH }px 1fr ${ INSPECTOR_WIDTH }px;
`;

const Area = styled.div`
  grid-area: ${ ( { area } ) => area };
  position: relative;
`;

function Layout() {
	return (
		<Editor>
			<Area area="head">
				<Header />
			</Area>
			<Area area="lib">
				<Library />
			</Area>
			<Area area="canv">
				<DropzoneProvider>
					<Canvas />
				</DropzoneProvider>
			</Area>
			<Area area="insp">
				<Inspector />
			</Area>
		</Editor>
	);
}

export default Layout;
