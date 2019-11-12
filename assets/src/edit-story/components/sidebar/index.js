/**
 * External dependencies
 */
import styled from 'styled-components';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v2 };
	padding: 16px 16px 0;
	height: 100%;
`;

const Panel = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	border: 1px solid ${ ( { theme } ) => theme.colors.fg.v2 };
	height: 100%;
`;

function Sidebar() {
	return (
		<Background>
			<Panel />
		</Background>
	);
}

export default Sidebar;
