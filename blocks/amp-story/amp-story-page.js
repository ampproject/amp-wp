import uuid from 'uuid/v4';
import LayerInserter from './layer-inserter';
import BlockNavigation from './block-navigation';
import {
	BLOCK_ICONS
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls
} = wp.editor;

const ALLOWED_BLOCKS = [
	'amp/amp-story-grid-layer-vertical',
	'amp/amp-story-grid-layer-fill',
	'amp/amp-story-grid-layer-thirds',
	'amp/amp-story-cta-layer'
];

const TEMPLATE = [
	[
		'amp/amp-story-grid-layer-vertical',
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Add content to layer.', 'amp' )
				}
			]
		]
	]
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-page',
	{
		title: __( 'Page', 'amp' ),
		category: 'layout',
		icon: BLOCK_ICONS[ 'amp/amp-story-page' ],

		// @todo Enforce that the amp-story-page can only be a root-level block; Using `parent: []` does not work, and it causes the inserter to be disabled entirely.
		attributes: {
			id: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-story-page',
				attribute: 'id'
			},
			backgroundColor: {
				type: 'string',
				default: '#ffffff'
			}
		},

		/*
		 * <amp-story-page>:
		 *   mandatory_parent: "AMP-STORY"
		 *   mandatory_min_num_child_tags: 1
		 *   child_tag_name_oneof: "AMP-ANALYTICS"
		 *   child_tag_name_oneof: "AMP-PIXEL"
		 *   child_tag_name_oneof: "AMP-STORY-CTA-LAYER"
		 *   child_tag_name_oneof: "AMP-STORY-GRID-LAYER"
		 *
		 * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L146-L171
		 * */

		edit( props ) {
			const { setAttributes, attributes } = props;
			const onChangeBackgroundColor = newBackgroundColor => {
				setAttributes( { backgroundColor: newBackgroundColor } );
			};

			// If the page ID is not set, add one.
			if ( ! attributes.id ) {
				setAttributes( { id: uuid() } );
			}

			return [
				<InspectorControls key='controls'>
					<PanelColorSettings
						title={ __( 'Background Color Settings', 'amp' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								value: attributes.backgroundColor,
								onChange: onChangeBackgroundColor,
								label: __( 'Background Color', 'amp' )
							}
						] }
					/>
				</InspectorControls>,
				<div className='editor-selectors'>
					<LayerInserter key="selectors" rootClientId={ props.clientId } />
					<BlockNavigation key='navigation' />
				</div>,
				// Get the template dynamically.
				<div key="contents" style={{ backgroundColor: attributes.backgroundColor }}>
					<InnerBlocks template={ TEMPLATE } allowedBlocks={ ALLOWED_BLOCKS } />
				</div>
			];
		},

		save( { attributes } ) {
			return (
				<amp-story-page style={{ backgroundColor: attributes.backgroundColor }} id={ attributes.id }>
					<InnerBlocks.Content />
				</amp-story-page>
			);
		}
	}
);
