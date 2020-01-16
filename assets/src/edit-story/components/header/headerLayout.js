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
	display: grid;
	grid:
    "header . buttons" 1fr
	/ 1fr;
`;

const Head = styled.header`
	grid-area: header;
	height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;
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

