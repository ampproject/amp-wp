/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { CENTRAL_RIGHT_PADDING, INSPECTOR_WIDTH } from '../../constants';
import Buttons from './buttons';

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

const Title = styled.h1`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0;
	font-size: 19px;
	line-height: 20px;
`;

const ButtonCell = styled.header`
	grid-area: buttons;
`;

function Header() {
	return (
		<Background>
			<Head>
				<Title>
					{ __( 'New story (click to edit title)' ) }
				</Title>
			</Head>
			<ButtonCell>
				<Buttons />
			</ButtonCell>
		</Background>
	);
}

export default Header;

