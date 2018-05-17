/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType,
	InspectorControls
} = wp.blocks;
const {
	DateTimePicker,
	PanelBody,
	TextControl,
	SelectControl
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-o2-player',
	{
		title: __( 'AMP O2 Player' ),
		category: 'common',
		icon: 'backup',
		keywords: [
			__( 'Embed' ),
			__( 'AOL O2Player' ),
		],

		attributes: {
			dataPid: {
				type: 'string'
			},
			dataVid: {
				type: 'number'
			},
			dataBcid: {
				type: 'string'
			},
			dataBid: {
				type: 'string'
			},
			layout: {
				type: 'string',
				default: 'fixed-height'
			},
			width: {
				type: 'number'
			},
			height: {
				type: 'number',
				default: 400
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataPid, dataVid, dataBcid, dataBid, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'fixed-height', label: 'Fixed height' },
				{ value: 'responsive', label: 'Responsive' },
				{ value: 'fixed', label: 'Fixed' },
				{ value: 'fill', label: 'Fill' },
				{ value: 'flex-item', label: 'Flex-item' },
				{ value: 'nodisplay', label: 'No Display' }

			];

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
								label={ __( 'Buyer Company ID (bcid, required)' ) }
								value={ dataBcid }
								onChange={ value => ( setAttributes( { dataBcid: value } ) ) }
							/>
							<TextControl
								label={ __( 'Playlist ID' ) }
								value={ dataBid }
								onChange={ value => ( setAttributes( { dataBid: value } ) ) }
							/>
							<TextControl
								label={ __( 'Video ID' ) }
								value={ dataVid }
								onChange={ value => ( setAttributes( { dataVid: value } ) ) }
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
								value={ height !== undefined ? height : '' }
								onChange={ value => ( setAttributes( { height: value } ) ) }
							/>
						</PanelBody>
					</InspectorControls>
				),
				<BlockControls key='controls'>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
						controls={ [ 'left', 'center', 'right' ] }
					/>
				</BlockControls>,
				<time key='timeago' dateTime={ attributes.dateTime }>{ timeAgo }</time>
			];
		},

		save( { attributes } ) {
			let timeagoProps = {
				layout: 'responsive',
				className: 'align' + ( attributes.align || 'none' ),
				datetime: attributes.dateTime,
				locale: 'en'
			};
			if ( attributes.cutoff ) {
				timeagoProps.cutoff = attributes.cutoff;
			}
			if ( attributes.ampLayout ) {
				switch ( attributes.ampLayout ) {
					case 'fixed-height':
						if ( attributes.height ) {
							timeagoProps.height = attributes.height;
							timeagoProps.layout = attributes.ampLayout;
						}
						break;
					case 'fixed':
						if ( attributes.height && attributes.width ) {
							timeagoProps.height = attributes.height;
							timeagoProps.width = attributes.width;
							timeagoProps.layout = attributes.ampLayout;
						}
						break;
				}
			}
			return (
				<amp-timeago { ...timeagoProps }>{ moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm' ) }</amp-timeago>
			);
		}
	}
);