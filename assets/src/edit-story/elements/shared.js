/**
 * External dependencies
 */
import { css } from 'styled-components';

export const ElementWithPosition = css`
	position: absolute;
	left: ${ ( { x } ) => `${ x }%` };
	top: ${ ( { y } ) => `${ y }%` };
`;

export const ElementWithSize = css`
	width: ${ ( { width } ) => `${ width }%` };
	height: ${ ( { height } ) => `${ height }%` };
`;

export const ElementWithBackgroundColor = css`
	background-color: ${ ( { backgroundColor } ) => backgroundColor };
`;

export const ElementWithFontColor = css`
	color: ${ ( { color } ) => color };
`;

export const ElementWithFont = css`
	font-family: ${ ( { fontFamily } ) => fontFamily };
	font-style: ${ ( { fontStyle } ) => fontStyle };
	font-size: ${ ( { fontSize } ) => fontSize };
	font-weight: ${ ( { fontWeight } ) => fontWeight };
`;
