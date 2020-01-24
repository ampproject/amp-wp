/**
 * External dependencies
 */
import styled from 'styled-components';

const Group = styled.label`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	display: flex;
	align-items: center;
	margin-bottom: 5px;
	opacity: ${ ( { disabled } ) => disabled ? 0.7 : 1 };
`;

export default Group;
