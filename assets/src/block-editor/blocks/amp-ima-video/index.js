/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-ima-video';

export const settings = {
	title: __( 'AMP IMA Video', 'amp' ),
	description: __( 'Embeds a video player for instream video ads that are integrated with the IMA SDK', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
	],

	// @todo Perhaps later add subtitles option and additional source options?
	attributes: {
		dataDelayAdRequest: {
			default: false,
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'data-delay-ad-request',
		},
		dataTag: {
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'data-tag',
		},
		dataSrc: {
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'data-src',
		},
		dataPoster: {
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'data-poster',
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-ima-video',
			attribute: 'height',
		},
	},

	edit,

	save,
};
