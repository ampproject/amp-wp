import {
	getGridLayerAttributes,
	saveGridLayer,
	editFillLayer
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;

const TEMPLATE = [
	[
		'core/image',
		{
			ampLayout: 'fill'
		}
	]
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-grid-layer-background-image',
	{
		title: __( 'Fill Image Layer', 'amp' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			return editFillLayer( props, TEMPLATE );
		},

		save( { attributes } ) {
			return saveGridLayer( attributes, 'fill' );
		}
	}
);
