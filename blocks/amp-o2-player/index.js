/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType,
	InspectorControls
} = wp.blocks;
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
	'amp/amp-o2-player',
	{
		title: __( 'AMP O2 Player' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' ),
			__( 'AOL O2Player' )
		],

		// @todo Add other useful macro toggles, e.g. showing relevant content.
		attributes: {
			dataPid: {
				type: 'string'
			},
			dataVid: {
				type: 'string'
			},
			dataBcid: {
				type: 'string'
			},
			dataBid: {
				type: 'string'
			},
			autoPlay: {
				type: 'boolean',
				default: false
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
			const { autoPlay, dataPid, dataVid, dataBcid, dataBid, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed-height', label: __( 'Fixed height' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) },
				{ value: 'nodisplay', label: __( 'No Display' ) }

			];
			let url = false;
			if ( dataPid && ( dataBcid || dataVid ) ) {
				url = 'https://delivery.vidible.tv/htmlembed/pid=' + dataPid + '/';
			}
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'O2 Player Settings' ) }>
							<TextControl
								label={ __( 'Player ID (required)' ) }
								value={ dataPid }
								onChange={ value => ( setAttributes( { dataPid: value } ) ) }
							/>
							<TextControl
								label={ __( 'Buyer Company ID (either buyer or video ID is required)' ) }
								value={ dataBcid }
								onChange={ value => ( setAttributes( { dataBcid: value } ) ) }
							/>
							<TextControl
								label={ __( 'Video ID (either buyer or video ID is required)' ) }
								value={ dataVid }
								onChange={ value => ( setAttributes( { dataVid: value } ) ) }
							/>
							<TextControl
								label={ __( 'Playlist ID' ) }
								value={ dataBid }
								onChange={ value => ( setAttributes( { dataBid: value } ) ) }
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
				),
				url && (
					<Placeholder label={ __( 'O2 Player' ) }>
						<p className="components-placeholder__error">{ url }</p>
						<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
					</Placeholder>
				),
				! url && (
					<Placeholder label={ __( 'O2 Player' ) }>
						<p>{ __( 'Add required data to use the block.' ) }</p>
					</Placeholder>
				)
			];
		},

		save( { attributes } ) {
			let o2Props = {
				layout: attributes.layout,
				height: attributes.height,
				'data-pid': attributes.dataPid
			};
			if ( 'fixed-height' !== attributes.layout && attributes.width ) {
				o2Props.width = attributes.width;
			}
			if ( ! attributes.autoPlay ) {
				o2Props[ 'data-macros' ] = 'm.playback=click';
			}
			if ( attributes.dataVid ) {
				o2Props[ 'data-vid' ] = attributes.dataVid;
			} else if ( attributes.dataBcid ) {
				o2Props[ 'data-bcid' ] = attributes.dataBcid;
			}
			if ( attributes.dataBid ) {
				o2Props[ 'data-bid' ] = attributes.dataBid;
			}
			return (
				<amp-o2-player { ...o2Props }></amp-o2-player>
			);
		}
	}
);
