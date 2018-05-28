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
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-ooyala-player',
	{
		title: __( 'AMP Ooyala Player' ),
		description: __( 'Displays an Ooyala video.' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' ),
			__( 'Ooyala video' )
		],

		// @todo Add data-config attribute?
		attributes: {
			dataEmbedCode: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'data-embedcode'
			},
			dataPlayerId: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'data-playerid'
			},
			dataPcode: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'data-pcode'
			},
			dataPlayerVersion: {
				type: 'string',
				default: 'v3',
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'data-playerversion'
			},
			layout: {
				type: 'string',
				default: 'fixed',
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-ooyala-player',
				attribute: 'height'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataEmbedCode, dataPlayerId, dataPcode, dataPlayerVersion, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) }

			];
			let url = false;
			if ( dataEmbedCode && dataPlayerId && dataPcode ) {
				url = `http://cf.c.ooyala.com/${dataEmbedCode}`;
			}
			return (
				<Fragment>
					{
						isSelected && (
							<InspectorControls key='inspector'>
								<PanelBody title={ __( 'Ooyala settings' ) }>
									<TextControl
										label={ __( 'Video embed code (required)' ) }
										value={ dataEmbedCode }
										onChange={ value => ( setAttributes( { dataEmbedCode: value } ) ) }
									/>
									<TextControl
										label={ __( 'Player ID (required)' ) }
										value={ dataPlayerId }
										onChange={ value => ( setAttributes( { dataPlayerId: value } ) ) }
									/>
									<TextControl
										label={ __( 'Provider code for the account (required)' ) }
										value={ dataPcode }
										onChange={ value => ( setAttributes( { dataPcode: value } ) ) }
									/>
									<SelectControl
										label={ __( 'Player version' ) }
										value={ dataPlayerVersion }
										options={ [
											{ value: 'v3', label: __( 'V3' ) },
											{ value: 'v4', label: __( 'V4' ) }
										] }
										onChange={ value => ( setAttributes( { dataPlayerVersion: value } ) ) }
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
						url && (
							<Placeholder label={ __( 'Ooyala Player' ) }>
								<p className="components-placeholder__error">{ url }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
							</Placeholder>
						)
					}
					{
						! url && (
							<Placeholder label={ __( 'Ooyala Player' ) }>
								<p>{ __( 'Add required data to use the block.' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			const { dataEmbedCode, dataPlayerId, dataPcode, dataPlayerVersion, layout, height, width } = attributes;

			let ooyalaProps = {
				layout: layout,
				height: height,
				'data-embedcode': dataEmbedCode,
				'data-playerid': dataPlayerId,
				'data-pcode': dataPcode,
				'data-playerversion': dataPlayerVersion
			};
			if ( 'fixed-height' !== layout && width ) {
				ooyalaProps.width = width;
			}
			return (
				<amp-ooyala-player { ...ooyalaProps }></amp-ooyala-player>
			);
		}
	}
);
