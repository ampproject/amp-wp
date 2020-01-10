/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-mathml';

export const settings = {
	title: __( 'AMP MathML', 'amp' ),
	category: 'common',
	icon: 'welcome-learn-more',
	keywords: [
		__( 'Mathematical formula', 'amp' ),
		__( 'Scientific content ', 'amp' ),
	],

	attributes: {
		dataFormula: {
			source: 'attribute',
			selector: 'amp-mathml',
			attribute: 'data-formula',
		},
	},

	edit,

	save,
};
