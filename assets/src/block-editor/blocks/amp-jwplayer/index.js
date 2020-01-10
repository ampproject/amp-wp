/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-jwplayer';

export const settings = {
	title: __( 'AMP JW Player', 'amp' ),
	description: __( 'Displays a cloud-hosted JW Player.', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
	],

	attributes: {
		dataPlayerId: {
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'data-player-id',
		},
		dataMediaId: {
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'data-media-id',
		},
		dataPlaylistId: {
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'data-playlist-id',
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-jwplayer',
			attribute: 'height',
		},
	},

	edit,

	save,
};
