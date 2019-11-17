/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { HEADER_HEIGHT } from '../../constants';
import LibraryContent from './libraryContent';
import LibraryTabs from './libraryTabs';

const Layout = styled.div`
	height: 100%;
	display: grid;
	grid:
		"tabs   " ${ HEADER_HEIGHT }px
		"library" 1fr
		/ 1fr;
`;

const TabsArea = styled.div`
	grid-area: tabs
`;

const LibraryBackground = styled.div`
	grid-area: library;
	background-color: ${ ( { theme } ) => theme.colors.bg.v4 };
	height: 100%;
	padding: 1em;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

function LibraryLayout() {
	return (
		<Layout>
			<TabsArea>
				<LibraryTabs />
			</TabsArea>
			<LibraryBackground>
				<LibraryContent />
			</LibraryBackground>
		</Layout>
	);
}

export default LibraryLayout;
