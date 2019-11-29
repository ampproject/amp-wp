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
import { useStory } from '../../app/story';

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

const Title = styled.input`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0;
	font-size: 19px;
	line-height: 20px;
	background: none !important;
	border: 0px none !important;
	color: #fff !important;
`;

const ButtonCell = styled.header`
	grid-area: buttons;
`;

function HeaderLayout() {

	const { state: { title }, actions: { setTitle } } = useStory();

	return (
		<Background>
			<Head>
				<Title
					value={ title }
					type={ 'text' }
					onChange={ ( evt ) => setTitle( evt.target.value ) }
				/>
			</Head>
			<ButtonCell>
				<Buttons />
			</ButtonCell>
		</Background>
	);
}

export default HeaderLayout;

