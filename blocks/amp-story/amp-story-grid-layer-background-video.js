import {
	getGridLayerAttributes,
	saveGridLayer,
	editFillLayer,
	BLOCK_ICONS
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;

const TEMPLATE = [
	[
		'core/video',
		{
			ampLayout: 'fill',
			autoplay: true
		}
	]
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-grid-layer-background-video',
	{
		title: __( 'Fill Video Layer', 'amp' ),
		category: 'layout',
		icon: BLOCK_ICONS[ 'amp/amp-story-grid-layer-background-video' ],
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
