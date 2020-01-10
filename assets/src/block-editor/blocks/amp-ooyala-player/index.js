/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-ooyala-player';

export const settings = {
	title: __( 'AMP Ooyala Player', 'amp' ),
	description: __( 'Displays an Ooyala video.', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
		__( 'Ooyala video', 'amp' ),
	],

	// @todo Add data-config attribute?
	attributes: {
		dataEmbedCode: {
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'data-embedcode',
		},
		dataPlayerId: {
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'data-playerid',
		},
		dataPcode: {
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'data-pcode',
		},
		dataPlayerVersion: {
			default: 'v3',
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'data-playerversion',
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-ooyala-player',
			attribute: 'height',
		},
	},

	edit,

	save,
};
