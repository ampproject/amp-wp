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
	'amp/amp-hulu',
	{
		title: __( 'AMP Hulu', 'amp' ),
		description: __( 'Displays a simple embedded Hulu video.', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		attributes: {
			dataEid: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-hulu',
				attribute: 'data-eid'
			},
			ampLayout: {
				type: 'string',
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-hulu',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-hulu',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-hulu',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataEid } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) }
			];
			let url = false;
			if ( dataEid ) {
				url = `https://player.hulu.com/site/dash/mobile_embed.html?eid=${dataEid}`;
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Hulu Settings', 'amp' ) }>
							<TextControl
								label={ __( 'The ID of the video (required)', 'amp' ) }
								value={ dataEid }
								onChange={ value => ( setAttributes( { dataEid: value } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'Hulu', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'Hulu', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let huluProps = {
				layout: attributes.ampLayout,
				height: attributes.height,
				'data-eid': attributes.dataEid
			};
			if ( 'fixed-height' !== attributes.ampLayout && attributes.width ) {
				huluProps.width = attributes.width;
			}
			return (
				<amp-hulu { ...huluProps }></amp-hulu>
			);
		}
	}
);
