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
	'amp/amp-story-grid-layer-thirds',
	{
		title: __( 'Thirds Layer', 'amp' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			return editGridLayer( props, 'thirds' );
		},

		save( { attributes } ) {
			return saveGridLayer( attributes, 'thirds' );
		}
	}
);
