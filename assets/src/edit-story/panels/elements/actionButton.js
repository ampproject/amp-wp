/**
 * External dependencies
 */
import styled from 'styled-components';

const ActionButton = styled.button.attrs( { type: 'button' } )`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 11px;
`;

export default ActionButton;
