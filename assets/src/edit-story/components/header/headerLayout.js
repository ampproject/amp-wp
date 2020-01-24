/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import Buttons from './buttons';
import Title from './title';

const Background = styled.div`
	display: flex;
	align-items: center;
	justify-content: space-between;
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
`;

const Head = styled.header`
	flex: 1 1 auto;
	padding: 1em;
`;

const ButtonCell = styled.header`
	grid-area: buttons;
`;

function HeaderLayout() {
	return (
		<Background>
			<Head>
				<Title />
			</Head>
			<ButtonCell>
				<Buttons />
			</ButtonCell>
		</Background>
	);
}

export default HeaderLayout;

