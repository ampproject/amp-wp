/* global ReactDOM */

import uuid from 'uuid/v4';
import BlockNavigation from './block-navigation';
import {
	BLOCK_ICONS,
	ALLOWED_BLOCKS,
} from './helpers';

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls,
} from '@wordpress/editor';
import { Component } from '@wordpress/element';
import { select } from '@wordpress/data';

const {
	hasSelectedInnerBlock,
	getSelectedBlockClientId,
} = select( 'core/editor' );

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
				attribute: 'id',
			},
			backgroundColor: {
				default: '#ffffff',
			},
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

			maybeAddBlockNavigation() {
				// If no blocks are selected or if it's the current page, change the view.
				if ( ! getSelectedBlockClientId() || this.props.clientId === getSelectedBlockClientId() || hasSelectedInnerBlock( this.props.clientId, true ) ) {
					const editLayout = document.getElementsByClassName( 'edit-post-layout' );
					if ( editLayout.length ) {
						const blockNav = document.getElementById( 'amp-root-navigation' );
						if ( ! blockNav ) {
							const navWrapper = document.createElement( 'div' );
							navWrapper.id = 'amp-root-navigation';
							editLayout[ 0 ].appendChild( navWrapper );
						}
						ReactDOM.render(
							<div key="layerManager" className="editor-selectors">
								<BlockNavigation />
							</div>,
							document.getElementById( 'amp-root-navigation' )
						);
					}
				}
			}

			componentDidMount() {
				this.maybeAddBlockNavigation();
			}

			componentDidUpdate() {
				// @todo Check if there is a better way to do this without calling it on both componentDidMount and componentDidUpdate.
				this.maybeAddBlockNavigation();
			}

			render() {
				const props = this.props;
				const { setAttributes, attributes } = props;
				const onChangeBackgroundColor = ( newBackgroundColor ) => {
					setAttributes( { backgroundColor: newBackgroundColor } );
				};

				return [
					<InspectorControls key="controls">
						<PanelColorSettings
							title={ __( 'Background Color Settings', 'amp' ) }
							initialOpen={ false }
							colorSettings={ [
								{
									value: attributes.backgroundColor,
									onChange: onChangeBackgroundColor,
									label: __( 'Background Color', 'amp' ),
								},
							] }
						/>
					</InspectorControls>,
					<div key="contents" style={ { backgroundColor: attributes.backgroundColor } }>
						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
					</div>,
				];
			}
		},

		save( { attributes } ) {
			return (
				<amp-story-page style={ { backgroundColor: attributes.backgroundColor } } id={ attributes.id }>
					{ /* @todo Add fill layer for image/video */ }
					<amp-story-grid-layer template="vertical">
						<InnerBlocks.Content />
					</amp-story-grid-layer>
					{ /* @todo Add amp-story-cta-layer */ }
				</amp-story-page>
			);
		},
	}
);
