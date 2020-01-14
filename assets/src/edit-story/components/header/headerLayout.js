/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { CENTRAL_RIGHT_PADDING, INSPECTOR_WIDTH } from '../../constants';
import Buttons from './buttons';
import Title from './title';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v3 };
	display: grid;
	grid:
    "header . buttons" 1fr
    / 1fr ${ CENTRAL_RIGHT_PADDING }px ${ INSPECTOR_WIDTH }px;
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

