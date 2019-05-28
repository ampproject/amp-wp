/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-springboard-player';

export const settings = {
	title: __( 'AMP Springboard Player', 'amp' ),
	description: __( 'Displays the Springboard Player used in the Springboard Video Platform', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
	],

	attributes: {
		dataSiteId: {
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-site-id',
		},
		dataContentId: {
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-content-id',
		},
		dataPlayerId: {
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-player-id',
		},
		dataDomain: {
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-domain',
		},
		dataMode: {
			default: 'video',
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-mode',
		},
		dataItems: {
			default: 1,
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'data-items',
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-springboard-player',
			attribute: 'height',
		},
	},

	edit,

	save,
};
