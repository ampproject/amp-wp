
/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import Header, { Buttons } from '../../components/header';
import Sidebar from '../../components/sidebar';
import Library, { LibraryTabs } from '../../components/library';
import Canvas, { AddPage, Meta, Carrousel } from '../../components/canvas';

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
    "tabs  head  head  head  btns  btns" 56px
    "lib   .     meta  .     .     side" 1fr
    "lib   .     page  add   .     side" 775px
    "lib   .     carr  .     .     side" 1fr
    / 355px 1fr 434px 1fr 46px 309px;
`;

const Area = styled.div`
  grid-area: ${ ( { area } ) => area };
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
			<Area area="tabs">
				<LibraryTabs />
			</Area>
			<Area area="page">
				<Canvas />
			</Area>
			<Area area="btns">
				<Buttons />
			</Area>
			<Area area="side">
				<Sidebar />
			</Area>
			<Area area="add">
				<AddPage />
			</Area>
			<Area area="meta">
				<Meta />
			</Area>
			<Area area="carr">
				<Carrousel />
			</Area>
		</Editor>
	);
}

export default Layout;
