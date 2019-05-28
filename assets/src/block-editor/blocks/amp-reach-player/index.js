/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-reach-player';

export const settings = {
	title: __( 'AMP Reach Player', 'amp' ),
	description: __( 'Displays the Reach Player configured in the Beachfront Reach platform.', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
		__( 'Beachfront Reach video', 'amp' ),
	],

	attributes: {
		dataEmbedId: {
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'data-embed-id',
		},
		ampLayout: {
			default: 'fixed-height',
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'height',
		},
	},

	edit,

	save,
};
