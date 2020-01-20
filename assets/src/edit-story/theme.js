/**
 * External dependencies
 */
import { createGlobalStyle } from 'styled-components';

export const GlobalStyle = createGlobalStyle`
	*,
	*::after,
	*::before { box-sizing: border-box; }
`;

const theme = {
	colors: {
		bg: {
			v0: '#000000',
			v1: '#191C28',
			v2: '#222636',
			v3: '#242A3B',
			v4: '#2F3449',
			v5: '#575D65',
			v6: '#1D222F',
			v7: '#07080C',
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
		selection: '#44aaff',
	},
	fonts: {
		body1: {
			family: 'Roboto',
			size: '16px',
			lineHeight: '24px',
			letterSpacing: '0.00625em',
		},
		body2: {
			family: 'Roboto',
			size: '14px',
			lineHeight: '20px',
			letterSpacing: '0.0142em',
		},
	},
};

export default theme;
