/* global ReactDOM */

import uuid from 'uuid/v4';
import BlockNavigation from './block-navigation';
import {
	BLOCK_ICONS,
	maybeIsSelectedParentClass
} from './helpers';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls,
	Inserter
} = wp.editor;

const { Component } = wp.element;

const ALLOWED_BLOCKS = [
	'amp/amp-story-grid-layer-vertical',
	'amp/amp-story-grid-layer-fill',
	'amp/amp-story-grid-layer-thirds',
	'amp/amp-story-cta-layer'
];

const {
	hasSelectedInnerBlock,
	getSelectedBlockClientId,
	getBlockIndex
} = wp.data.select( 'core/editor' );

const TEMPLATE = [
	[ 'amp/amp-story-grid-layer-background-image' ],
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
		attributes: {
			id: {
				source: 'attribute',
				selector: 'amp-story-page',
				attribute: 'id'
			},
			backgroundColor: {
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

		edit: class extends Component {
			constructor( props ) {
				// Call parent constructor.
				super( props );

				if ( ! props.attributes.id ) {
					this.props.setAttributes( { id: uuid() } );
				}
			}

			componentDidUpdate() {
				// If no blocks are selected or if it's the current page, change the view.
				if ( ! getSelectedBlockClientId() || this.props.clientId === getSelectedBlockClientId() || hasSelectedInnerBlock( this.props.clientId, true ) ) {
					const editLayout = document.getElementsByClassName( 'edit-post-layout' );
					if ( editLayout.length ) {
						const blockNav = document.getElementById( 'amp-root-navigation' );
						if ( ! blockNav ) {
							let navWrapper = document.createElement( 'div' );
							navWrapper.id = 'amp-root-navigation';
							editLayout[ 0 ].appendChild( navWrapper );
						}
						let navList;
						if ( hasSelectedInnerBlock( this.props.clientId, true ) || this.props.isSelected ) {
							let className = 'editor-selectors';
							if ( 0 === getBlockIndex( this.props.clientId ) ) {
								className += ' amp-story-page-first';
							}
							navList =
								<div key='layerManager' className={ className }>
									<Inserter rootClientId={ this.props.clientId } />
									<BlockNavigation />
								</div>;
						} else {
							navList =
								<div key='layerManager' className='editor-selectors'>
									<BlockNavigation />
								</div>;
						}
						ReactDOM.render( navList, document.getElementById( 'amp-root-navigation' ) );
					}
				}
			}

			render() {
				const props = this.props;
				const { setAttributes, attributes } = props;
				const onChangeBackgroundColor = newBackgroundColor => {
					setAttributes( { backgroundColor: newBackgroundColor } );
				};

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
					<div key="contents" className={ maybeIsSelectedParentClass( props.clientId ) } style={{ backgroundColor: attributes.backgroundColor }}>
						<InnerBlocks template={ TEMPLATE } allowedBlocks={ ALLOWED_BLOCKS } />
					</div>
				];
			}
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
