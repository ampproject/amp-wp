/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { HEADER_HEIGHT } from '../../constants';
import LibraryProvider from './libraryProvider';
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

const Tabs = styled.div`
	grid-area: tabs
`;

const Background = styled.div`
	grid-area: library;
	background-color: ${ ( { theme } ) => theme.colors.bg.v4 };
	height: 100%;
	padding: 1em;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

function Library() {
	return (
		<LibraryProvider>
			<Layout>
				<Tabs>
					<LibraryTabs />
				</Tabs>
				<Background>
					<LibraryContent />
				</Background>
			</Layout>
		</LibraryProvider>
	);
}

export default Library;
