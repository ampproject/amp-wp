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
		title: __( 'AMP IMA Video' ),
		description: __( 'Embeds a video player for instream video ads that are integrated with the IMA SDK' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' )
		],

		// @todo Perhaps later add subtitles option and additional source options?
		attributes: {
			dataDelayAdRequest: {
				default: false
			},
			dataTag: {
				type: 'string'
			},
			dataSrc: {
				type: 'string'
			},
			dataPoster: {
				type: 'string'
			},
			layout: {
				type: 'string',
				default: 'responsive'
			},
			width: {
				type: 'number',
				default: 600
			},
			height: {
				type: 'number',
				default: 400
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataDelayAdRequest, dataTag, dataSrc, dataPoster, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed', label: __( 'Fixed' ) }

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
								<PanelBody title={ __( 'IMA Video Settings' ) }>
									<TextControl
										label={ __( 'Https URL for your VAST ad document (required)' ) }
										value={ dataTag }
										onChange={ value => ( setAttributes( { dataTag: value } ) ) }
									/>
									<TextControl
										label={ __( 'Https URL of your video content (required)' ) }
										value={ dataSrc }
										onChange={ value => ( setAttributes( { dataSrc: value } ) ) }
									/>
									<TextControl
										label={ __( 'Https URL to preview image' ) }
										value={ dataPoster }
										onChange={ value => ( setAttributes( { dataPoster: value } ) ) }
									/>
									<ToggleControl
										label={ __( 'Delay Ad Request' ) }
										checked={ dataDelayAdRequest }
										onChange={ () => ( setAttributes( { dataDelayAdRequest: ! dataDelayAdRequest } ) ) }
									/>
									<SelectControl
										label={ __( 'Layout' ) }
										value={ layout }
										options={ ampLayoutOptions }
										onChange={ value => ( setAttributes( { layout: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Width (px)' ) }
										value={ width !== undefined ? width : '' }
										onChange={ value => ( setAttributes( { width: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Height (px)' ) }
										value={ height }
										onChange={ value => ( setAttributes( { height: value } ) ) }
									/>
								</PanelBody>
							</InspectorControls>
						)
					}
					{
						dataSet && (
							<Placeholder label={ __( 'IMA Video' ) }>
								<p className="components-placeholder__error">{ dataSrc }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
							</Placeholder>
						)
					}
					{
						! dataSet && (
							<Placeholder label={ __( 'IMA Video' ) }>
								<p>{ __( 'Add required data to use the block.' ) }</p>
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
