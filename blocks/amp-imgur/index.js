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
	'amp/amp-imgur',
	{
		title: __( 'AMP Imgur', 'amp' ),
		description: __( 'Displays an Imgur post.', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		attributes: {
			dataImgurId: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-imgur',
				attribute: 'data-imgur-id'
			},
			ampLayout: {
				type: 'string',
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-imgur',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-imgur',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-imgur',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataImgurId } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
				{ value: 'nodisplay', label: __( 'No Display', 'amp' ) }
			];
			let url = false;
			if ( dataImgurId ) {
				url = `https://imgur.com/${dataImgurId}/embed?pub=true`;
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Imgur Settings', 'amp' ) }>
							<TextControl
								label={ __( 'The ID of the video (required)', 'amp' ) }
								value={ dataImgurId }
								onChange={ value => ( setAttributes( { dataImgurId: value } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'Imgur', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'Imgur', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let imgurProps = {
				layout: attributes.ampLayout,
				height: attributes.height,
				'data-imgur-id': attributes.dataImgurId
			};
			if ( 'fixed-height' !== attributes.ampLayout && attributes.width ) {
				imgurProps.width = attributes.width;
			}
			return (
				<amp-imgur { ...imgurProps }></amp-imgur>
			);
		}
	}
);
