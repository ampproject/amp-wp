import {
	getGridLayerAttributes,
	saveGridLayer,
	editFillLayer,
	BLOCK_ICONS,
} from './helpers';

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

const TEMPLATE = [
	[
		'core/image',
		{
			ampLayout: 'fill',
		},
	],
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-grid-layer-background-image',
	{
		title: __( 'Fill Image Layer', 'amp' ),
		category: 'layout',
		icon: BLOCK_ICONS[ 'amp/amp-story-grid-layer-background-image' ],
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			return editFillLayer( props, TEMPLATE );
		},

		save( { attributes } ) {
			return saveGridLayer( attributes, 'fill' );
		},
	}
);
