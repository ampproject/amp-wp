/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-brid-player';

export const settings =	{
	title: __( 'AMP Brid Player', 'amp' ),
	description: __( 'Displays the Brid Player used in Brid.tv Video Platform.', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
	],

	attributes: {
		autoPlay: {
			type: 'boolean',
		},
		dataPartner: {
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'data-partner',
		},
		dataPlayer: {
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'data-player',
		},
		dataVideo: {
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'data-video',
		},
		dataPlaylist: {
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'data-playlist',
		},
		dataOutstream: {
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'data-outstream',
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'layout',
		},
		width: {
			type: 'number',
			default: 600,
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-brid-player',
			attribute: 'height',
		},
	},

	edit,

	save,
};
