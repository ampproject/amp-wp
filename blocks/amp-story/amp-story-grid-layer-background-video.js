/* eslint no-magic-numbers: [ "error", { "ignore": [ 1 ] } ] */

import {
	getAmpStoryAnimationControls,
	getAmpGridLayerBackgroundSettings,
	getGridLayerAttributes,
	saveFillGridLayer
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InspectorControls,
	InnerBlocks
} = wp.editor;
const {
	PanelBody
} = wp.components;

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
		title: __( 'Background Video Layer', 'amp' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],
		attributes: getGridLayerAttributes(),
		inserter: false,

		edit( props ) {
			const { setAttributes, attributes } = props;

			return [
				<InspectorControls key='inspector'>
					{
						getAmpGridLayerBackgroundSettings( setAttributes, attributes )
					}
					<PanelBody key='animation' title={ __( 'Video Layer Animation', 'amp' ) }>
						{
							getAmpStoryAnimationControls( setAttributes, attributes )
						}
					</PanelBody>
				</InspectorControls>,
				<div key='contents' style={{ opacity: attributes.opacity, backgroundColor: attributes.backgroundColor }} className='amp-grid-template amp-grid-template-fill'>
					<InnerBlocks template={ TEMPLATE } templateLock='all' />
				</div>
			];
		},

		save( { attributes } ) {
			return saveFillGridLayer( attributes );
		}
	}
);
