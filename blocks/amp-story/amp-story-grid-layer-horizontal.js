import {
	getGridLayerAttributes,
	saveGridLayer,
	editGridLayer
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-grid-layer-horizontal',
	{
		title: __( 'Horizontal Layer', 'amp' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			return editGridLayer( props, 'horizontal' );
		},

		save( { attributes } ) {
			return saveGridLayer( attributes, 'horizontal' );
		}
	}
);
