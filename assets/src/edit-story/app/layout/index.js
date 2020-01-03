
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
import BitmapRenderer from '../../components/bitmapRenderer';
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

const Fixed = styled.div`
  position: fixed;
  bottom: 10px;
  right: 10px;
  z-index: 1000;
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
				<Canvas />
			</Area>
			<Area area="insp">
				<Inspector />
			</Area>
			<Fixed>
				<BitmapRenderer />
			</Fixed>
		</Editor>
	);
}

export default Layout;
