/* eslint no-magic-numbers: [ "error", { "ignore": [ 1 ] } ] */

import {
	ALLOWED_BLOCKS,
	getAmpStoryAnimationControls,
	getAmpGridLayerBackgroundSettings,
	getGridLayerAttributes
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

		/*
		 * <amp-story-grid-layer>:
		 *   mandatory_ancestor: "AMP-STORY-PAGE"
		 *   descendant_tag_list: "amp-story-grid-layer-allowed-descendants"
		 *
		 * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L172-L188
		 */

		edit( props ) {
			const { setAttributes, attributes } = props;

			return [
				<InspectorControls key='inspector'>
					{
						getAmpGridLayerBackgroundSettings( setAttributes, attributes )
					}
					<PanelBody key='animation' title={ __( 'Grid Layer Animation', 'amp' ) }>
						{
							getAmpStoryAnimationControls( setAttributes, attributes )
						}
					</PanelBody>
				</InspectorControls>,
				<div key='contents' style={{ opacity: attributes.opacity, backgroundColor: attributes.backgroundColor }} className='amp-grid-template amp-grid-template-horizontal'>
					<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
				</div>
			];
		},

		save( { attributes } ) {
			let layerProps = {
					template: 'horizontal'
				},
				style = {};
			if ( attributes.animationType ) {
				layerProps[ 'animate-in' ] = attributes.animationType;

				if ( attributes.animationDelay ) {
					layerProps[ 'animate-in-delay' ] = attributes.animationDelay;
				}
				if ( attributes.animationDuration ) {
					layerProps[ 'animate-in-duration' ] = attributes.animationDuration;
				}
			}

			if ( 1 !== attributes.opacity ) {
				style.opacity = attributes.opacity;
			}
			if ( attributes.backgroundColor ) {
				style.backgroundColor = attributes.backgroundColor;
			}
			if ( ! _.isEmpty( style ) ) {
				layerProps.style = style;
			}

			return (
				<amp-story-grid-layer { ...layerProps }>
					<InnerBlocks.Content />
				</amp-story-grid-layer>
			);
		}
	}
);
