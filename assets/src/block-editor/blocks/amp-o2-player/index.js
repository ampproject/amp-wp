/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-o2-player';

export const settings = {
	title: __( 'AMP O2 Player', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
		__( 'AOL O2Player', 'amp' ),
	],

	// @todo Add other useful macro toggles, e.g. showing relevant content.
	attributes: {
		dataPid: {
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'data-pid',
		},
		dataVid: {
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'data-vid',
		},
		dataBcid: {
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'data-bcid',
		},
		dataBid: {
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'data-bid',
		},
		autoPlay: {
			default: false,
		},
		ampLayout: {
			default: 'responsive',
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-o2-player',
			attribute: 'height',
		},
	},

	edit,

	save,
};
