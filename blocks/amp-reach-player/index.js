/**
 * Helper methods for blocks.
 */
import { getLayoutControls, getMediaPlaceholder } from '../utils.js';

/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { Fragment } = wp.element;
const {
	PanelBody,
	TextControl,
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-reach-player',
	{
		title: __( 'AMP Reach Player', 'amp' ),
		description: __( 'Displays the Reach Player configured in the Beachfront Reach platform.', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' ),
			__( 'Beachfront Reach video', 'amp' )
		],

		attributes: {
			dataEmbedId: {
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'data-embed-id'
			},
			ampLayout: {
				default: 'fixed-height',
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'layout'
			},
			width: {
				default: 600,
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'width'
			},
			height: {
				default: 400,
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataEmbedId } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed Height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) }

			];
			let url = false;
			if ( dataEmbedId ) {
				url = 'https://media-cdn.beachfrontreach.com/acct_1/video/';
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Reach settings', 'amp' ) }>
							<TextControl
								label={ __( 'The Reach player embed id (required)', 'amp' ) }
								value={ dataEmbedId }
								onChange={ value => ( setAttributes( { dataEmbedId: value } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'Reach Player', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'Reach Player', 'amp' ) }>
								<p>{ __( 'Add Reach player embed ID to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			const { dataEmbedId, ampLayout, height, width } = attributes;

			let reachProps = {
				layout: ampLayout,
				height: height,
				'data-embed-id': dataEmbedId
			};
			if ( 'fixed-height' !== ampLayout && width ) {
				reachProps.width = width;
			}
			return (
				<amp-reach-player { ...reachProps }></amp-reach-player>
			);
		}
	}
);
