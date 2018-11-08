import {
	getGridLayerAttributes,
	saveGridLayer,
	editGridLayer,
	BLOCK_ICONS
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
		icon: BLOCK_ICONS[ 'amp/amp-story-grid-layer-thirds' ],
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
