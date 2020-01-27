
/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import Inspector from '../../components/inspector';
import Library from '../../components/library';
import Canvas from '../../components/canvas';
import { ADMIN_TOOLBAR_HEIGHT, LIBRARY_MIN_WIDTH, LIBRARY_MAX_WIDTH, INSPECTOR_MIN_WIDTH, INSPECTOR_MAX_WIDTH } from '../../constants';
import DropZoneProvider from '../../components/dropzone/dropZoneProvider';

const Editor = styled.div`
	font-family: ${ ( { theme } ) => theme.fonts.body1.family };
	font-size: ${ ( { theme } ) => theme.fonts.body1.size };
	line-height: ${ ( { theme } ) => theme.fonts.body1.lineHeight };
	letter-spacing: ${ ( { theme } ) => theme.fonts.body1.letterSpacing };
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	position: absolute;
	left: -20px;
	top: 0;
	right: 0;
	bottom: 0;
	height: calc(100vh - ${ ADMIN_TOOLBAR_HEIGHT }px);

	display: grid;
	grid:
		"lib  canv  insp" 1fr
		/ minmax(${ LIBRARY_MIN_WIDTH }px, ${ LIBRARY_MAX_WIDTH }px) 1fr minmax(${ INSPECTOR_MIN_WIDTH }px, ${ INSPECTOR_MAX_WIDTH }px);
`;

// @todo: set `overflow: hidden;` once page size is responsive.
const Area = styled.div`
  grid-area: ${ ( { area } ) => area };
  position: relative;
  z-index: ${ ( { area } ) => area === 'canv' ? 1 : 2 };
`;

function Layout() {
	return (
		<Editor>
			<Area area="lib">
				<Library />
			</Area>
			<Area area="canv">
				<DropZoneProvider>
					<Canvas />
				</DropZoneProvider>
			</Area>
			<Area area="insp">
				<Inspector />
			</Area>
		</Editor>
	);
}

export default Layout;
