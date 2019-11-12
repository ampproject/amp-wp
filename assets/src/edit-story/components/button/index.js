/**
 * External dependencies
 */
import styled from 'styled-components';

const Base = styled.button`
	border-width: 1px;
	border-style: solid;
	border-radius: 4px;
	background: transparent;
	display: block;
	min-width: 63px;
	line-height: 28px;
	height: 30px;
	padding: 0 10px;
	cursor: pointer;

	&:focus, &:active {
		outline: none;
	}
`;

export const Primary = styled( Base )`
	border-color: ${ ( { theme } ) => theme.colors.action };
	background-color: ${ ( { theme } ) => theme.colors.action };
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

export const Secondary = styled( Base )`
	border-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	background-color: ${ ( { theme } ) => theme.colors.fg.v3 };
	color: ${ ( { theme } ) => theme.colors.bg.v5 };
`;

export const Outline = styled( Base )`
	border-color: ${ ( { theme } ) => theme.colors.fg.v2 };
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;
