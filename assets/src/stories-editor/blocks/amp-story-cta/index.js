/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createBlock, getBlockAttributes } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';
import deprecated from './deprecated';
import blockIcon from '../../../../images/stories-editor/call-to-action.svg';

const schema = {
	url: {
		type: 'string',
		source: 'attribute',
		selector: 'a',
		attribute: 'href',
	},
	text: {
		type: 'string',
		source: 'html',
		selector: 'a',
	},
	// The rest of the color attributes are added by addAMPAttributes()
	customTextColor: {
		type: 'string',
		default: '#ffffff',
	},
	customBackgroundColor: {
		type: 'string',
		default: '#32373c',
	},
	btnPositionTop: {
		type: 'number',
		default: 0,
	},
	btnPositionLeft: {
		type: 'number',
		default: 30,
	},
};

export const name = 'amp/amp-story-cta';

export const settings = {
	title: __( 'Call to Action', 'amp' ),

	description: __( 'Prompt visitors to take action with a button-style link.', 'amp' ),

	icon: blockIcon,

	category: 'layout',

	keywords: [
		__( 'cta', 'amp' ),
		__( 'button', 'amp' ),
	],

	attributes: schema,

	edit,

	save,

	deprecated,

	transforms: {
		from: [
			{
				type: 'raw',
				priority: 20,
				selector: 'amp-story-cta-layer',
				transform: ( node ) => {
					const innerHTML = node.outerHTML;
					const blockAttributes = getBlockAttributes( name, innerHTML );

					return createBlock( name, blockAttributes );
				},
			},
		],
	},
};
