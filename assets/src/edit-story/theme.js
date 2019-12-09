/**
 * External dependencies
 */
import { createGlobalStyle } from 'styled-components';

export const GlobalStyle = createGlobalStyle`
	* { box-sizing: border-box; }
`;

const theme = {
	colors: {
		bg: {
			v1: '#191C28',
			v2: '#222636',
			v3: '#242A3B',
			v4: '#2F3449',
			v5: '#575D65',
		},
		mg: {
			v1: '#616877',
			v2: '#DADADA',
		},
		fg: {
			v1: '#FFFFFF',
			v2: '#E5E5E5',
			v3: '#D4D3D4',
			v4: '#B3B3B3',
		},
		action: '#47A0F4',
		danger: '#FF0000',
	},
};

export default theme;
