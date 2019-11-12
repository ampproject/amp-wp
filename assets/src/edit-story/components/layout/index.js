
/**
 * External dependencies
 */
import styled, { ThemeProvider } from 'styled-components';

/**
 * WordPress dependencies
 */
import {
	Popover,
	SlotFillProvider,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import Header, { Buttons } from '../header';
import Sidebar from '../sidebar';
import Explorer, { ExplorerProvider, ExplorerTabs } from '../explorer';
import Canvas, { AddPage, Meta, Carrousel } from '../canvas';
import darkTheme, { GlobalStyle } from '../../theme';

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
    "tabs      header  header  header  buttons  buttons" 56px
    "explorer  empty1  meta    empty2  empty4   sidebar" 1fr
    "explorer  empty1  canvas  add     empty4   sidebar" 775px
    "explorer  empty1  pages   empty3  empty4   sidebar" 1fr
    / 355px 1fr 434px 1fr 46px 309px;
`;

const Area = styled.div`
  grid-area: ${ ( { area } ) => area };
`;

function Layout() {
	return (
		<SlotFillProvider>
			<ThemeProvider theme={ darkTheme }>
				<ExplorerProvider>
					<GlobalStyle />
					<Editor>
						<Area area="header">
							<Header />
						</Area>
						<Area area="explorer">
							<Explorer />
						</Area>
						<Area area="tabs">
							<ExplorerTabs />
						</Area>
						<Area area="canvas">
							<Canvas />
						</Area>
						<Area area="buttons">
							<Buttons />
						</Area>
						<Area area="sidebar">
							<Sidebar />
						</Area>
						<Area area="add">
							<AddPage />
						</Area>
						<Area area="meta">
							<Meta />
						</Area>
						<Area area="pages">
							<Carrousel />
						</Area>
					</Editor>
					<Popover.Slot />
				</ExplorerProvider>
			</ThemeProvider>
		</SlotFillProvider>
	);
}

export default Layout;
