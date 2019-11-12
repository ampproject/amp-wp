/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

const Background = styled.aside`
	background-color: ${ ( { theme } ) => theme.colors.bg.v4 };
	height: 100%;
	padding: 1em;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

function Explorer() {
	const { tab } = useContext( Context );
	return (
		<Background>
			{ 'Displaying: ' }
			{ tab }
		</Background>
	);
}

export default Explorer;
