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
	'amp/amp-story-grid-layer-vertical',
	{
		title: __( 'Vertical Layer', 'amp' ),
		category: 'layout',
		icon: BLOCK_ICONS[ 'amp/amp-story-grid-layer-vertical' ],
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			return editGridLayer( props, 'vertical' );
		},

		save( { attributes } ) {
			return saveGridLayer( attributes, 'vertical' );
		}
	}
);
