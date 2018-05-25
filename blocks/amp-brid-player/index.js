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
	'amp/amp-brid-player',
	{
		title: __( 'AMP Brid Player' ),
		description: __( 'Displays the Brid Player used in Brid.tv Video Platform.' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' )
		],

		attributes: {
			autoPlay: {
				default: false
			},
			dataPartner: {
				type: 'number'
			},
			dataPlayer: {
				type: 'number'
			},
			dataVideo: {
				type: 'number'
			},
			dataPlaylist: {
				type: 'number'
			},
			dataOutstream: {
				type: 'number'
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
			const { autoPlay, dataPartner, dataPlayer, dataVideo, dataPlaylist, dataOutstream, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed-height', label: __( 'Fixed height' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) },
				{ value: 'nodisplay', label: __( 'No Display' ) }

			];
			let url = false;
			if ( dataPartner && dataPlayer && ( dataVideo || dataPlaylist || dataOutstream ) ) {
				url = `http://cdn.brid.tv/live/partners/${dataPartner}`;
			}
			return (
				<Fragment>
					{
						isSelected && (
							<InspectorControls key='inspector'>
								<PanelBody title={ __( 'Brid Player Settings' ) }>
									<TextControl
										label={ __( 'Brid.tv partner ID (required)' ) }
										value={ dataPartner }
										onChange={ value => ( setAttributes( { dataPartner: value } ) ) }
									/>
									<TextControl
										label={ __( 'Brid.tv player ID (required)' ) }
										value={ dataPlayer }
										onChange={ value => ( setAttributes( { dataPlayer: value } ) ) }
									/>
									<TextControl
										label={ __( 'Video ID (one of video / playlist / outstream ID is required)' ) }
										value={ dataVideo }
										onChange={ value => ( setAttributes( { dataVideo: value } ) ) }
									/>
									<TextControl
										label={ __( 'Outstream unit ID (one of video / playlist / outstream ID is required)' ) }
										value={ dataOutstream }
										onChange={ value => ( setAttributes( { dataOutstream: value } ) ) }
									/>
									<TextControl
										label={ __( 'Playlist ID (one of video / playlist / outstream ID is required)' ) }
										value={ dataPlaylist }
										onChange={ value => ( setAttributes( { dataPlaylist: value } ) ) }
									/>
									<ToggleControl
										label={ __( 'Autoplay' ) }
										checked={ autoPlay }
										onChange={ () => ( setAttributes( { autoPlay: ! autoPlay } ) ) }
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
							<Placeholder label={ __( 'Brid Player' ) }>
								<p className="components-placeholder__error">{ url }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
							</Placeholder>
						)

					}
					{
						! url && (
							<Placeholder label={ __( 'Brid Player' ) }>
								<p>{ __( 'Add required data to use the block.' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let bridProps = {
				layout: attributes.layout,
				height: attributes.height,
				'data-player': attributes.dataPlayer,
				'data-partner': attributes.dataPartner
			};
			if ( 'fixed-height' !== attributes.layout && attributes.width ) {
				bridProps.width = attributes.width;
			}
			if ( attributes.dataPlaylist ) {
				bridProps[ 'data-playlist' ] = attributes.dataPlaylist;
			}
			if ( attributes.dataVideo ) {
				bridProps[ 'data-video' ] = attributes.dataVideo;
			}
			if ( attributes.dataOutstream ) {
				bridProps[ 'data-outstream' ] = attributes.dataOutstream;
			}
			if ( attributes.autoPlay ) {
				bridProps.autoplay = attributes.autoPlay;
			}
			return (
				<amp-brid-player { ...bridProps }></amp-brid-player>
			);
		}
	}
);
