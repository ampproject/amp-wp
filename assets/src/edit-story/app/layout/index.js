
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
import { LIBRARY_MIN_WIDTH, LIBRARY_MAX_WIDTH, INSPECTOR_MIN_WIDTH, INSPECTOR_MAX_WIDTH, HEADER_HEIGHT } from '../../constants';

const Editor = styled.div`
	font-family: ${ ( { theme } ) => theme.fonts.display.family };
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	position: absolute;
	left: -20px;
	top: 0;
	right: 0;
	bottom: 0;
	min-height: calc(100vh - 32px);

	display: grid;
	grid:
		"lib  head  insp" ${ HEADER_HEIGHT }px
		"lib  canv  insp" 1fr
		/ minmax(${ LIBRARY_MIN_WIDTH }px, ${ LIBRARY_MAX_WIDTH }px) 1fr minmax(${ INSPECTOR_MIN_WIDTH }px, ${ INSPECTOR_MAX_WIDTH }px);
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
				<Canvas />
			</Area>
			<Area area="insp">
				<Inspector />
			</Area>
		</Editor>
	);
}

export default Layout;
