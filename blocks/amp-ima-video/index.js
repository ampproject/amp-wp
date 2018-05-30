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
	SelectControl,
	Placeholder,
	ToggleControl
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-ima-video',
	{
		title: __( 'AMP IMA Video', 'amp' ),
		description: __( 'Embeds a video player for instream video ads that are integrated with the IMA SDK', 'amp' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		// @todo Perhaps later add subtitles option and additional source options?
		attributes: {
			dataDelayAdRequest: {
				default: false,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-delay-ad-request'
			},
			dataTag: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-tag'
			},
			dataSrc: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-src'
			},
			dataPoster: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-poster'
			},
			layout: {
				type: 'string',
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'height'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataDelayAdRequest, dataTag, dataSrc, dataPoster, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) }

			];
			let dataSet = false;
			if ( dataTag && dataSrc ) {
				dataSet = true;
			}
			return (
				<Fragment>
					{
						isSelected && (
							<InspectorControls key='inspector'>
								<PanelBody title={ __( 'IMA Video Settings', 'amp' ) }>
									<TextControl
										label={ __( 'Https URL for your VAST ad document (required)', 'amp' ) }
										value={ dataTag }
										onChange={ value => ( setAttributes( { dataTag: value } ) ) }
									/>
									<TextControl
										label={ __( 'Https URL of your video content (required)', 'amp' ) }
										value={ dataSrc }
										onChange={ value => ( setAttributes( { dataSrc: value } ) ) }
									/>
									<TextControl
										label={ __( 'Https URL to preview image', 'amp' ) }
										value={ dataPoster }
										onChange={ value => ( setAttributes( { dataPoster: value } ) ) }
									/>
									<ToggleControl
										label={ __( 'Delay Ad Request', 'amp' ) }
										checked={ dataDelayAdRequest }
										onChange={ () => ( setAttributes( { dataDelayAdRequest: ! dataDelayAdRequest } ) ) }
									/>
									<SelectControl
										label={ __( 'Layout', 'amp' ) }
										value={ layout }
										options={ ampLayoutOptions }
										onChange={ value => ( setAttributes( { layout: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Width (px)', 'amp' ) }
										value={ width !== undefined ? width : '' }
										onChange={ value => ( setAttributes( { width: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Height (px)', 'amp' ) }
										value={ height }
										onChange={ value => ( setAttributes( { height: value } ) ) }
									/>
								</PanelBody>
							</InspectorControls>
						)
					}
					{
						dataSet && (
							<Placeholder label={ __( 'IMA Video', 'amp' ) }>
								<p className="components-placeholder__error">{ dataSrc }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!', 'amp' ) }</p>
							</Placeholder>
						)
					}
					{
						! dataSet && (
							<Placeholder label={ __( 'IMA Video', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let imaProps = {
				layout: attributes.layout,
				height: attributes.height,
				width: attributes.width,
				'data-tag': attributes.dataTag,
				'data-src': attributes.dataSrc
			};
			if ( attributes.dataPoster ) {
				imaProps[ 'data-poster' ] = attributes.dataPoster;
			}
			if ( attributes.dataDelayAdRequest ) {
				imaProps[ 'data-delay-ad-request' ] = attributes.dataDelayAdRequest;
			}
			return (
				<amp-ima-video { ...imaProps }></amp-ima-video>
			);
		}
	}
);
