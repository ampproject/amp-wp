/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';

const List = styled.nav`
	display: flex;
	flex-direction: row;
	align-items: flex-start;
	justify-content: center;
	height: 100%;
	padding-top: 1em;
`;

const Page = styled.a`
	background-color: ${ ( { isActive, theme } ) => isActive ? theme.colors.fg.v1 : theme.colors.mg.v1 };
	height: 48px;
	width: 27px;
	margin: 0 5px;
	cursor: pointer;

	&:hover {
		background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

function Canvas() {
	const { state: { pages, currentPageId }, actions: { setCurrentPageById } } = useStory();
	return (
		<List>
			{ pages.map( ( { clientId } ) => (
				<Page key={ clientId } onClick={ () => setCurrentPageById( clientId ) } isActive={ clientId === currentPageId } />
			) ) }
		</List>
	);
}

export default Canvas;
