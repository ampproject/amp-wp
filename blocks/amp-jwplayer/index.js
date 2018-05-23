/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
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
	'amp/amp-jwplayer',
	{
		title: __( 'AMP JW Player' ),
		description: __( 'Displays a cloud-hosted JW Player.' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' )
		],

		attributes: {
			dataPlayerId: {
				type: 'string'
			},
			dataMediaId: {
				type: 'string'
			},
			dataPlaylistId: {
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
			const { dataPlayerId, dataMediaId, dataPlaylistId, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed-height', label: __( 'Fixed height' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) },
				{ value: 'nodisplay', label: __( 'No Display' ) }

			];
			let url = false;
			if ( dataPlayerId && ( dataMediaId || dataPlaylistId ) ) {
				if ( dataPlaylistId ) {
					url = `https://content.jwplatform.com/players/${dataPlaylistId}-${dataPlayerId}`;
				} else {
					url = `https://content.jwplatform.com/players/${dataMediaId}-${dataPlayerId}`;
				}
			}
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'JW Player Settings' ) }>
							<TextControl
								label={ __( 'Player ID (required)' ) }
								value={ dataPlayerId }
								onChange={ value => ( setAttributes( { dataPlayerId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Media ID (required if playlist ID not set)' ) }
								value={ dataMediaId }
								onChange={ value => ( setAttributes( { dataMediaId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Playlist ID (required if media ID not set)' ) }
								value={ dataPlaylistId }
								onChange={ value => ( setAttributes( { dataPlaylistId: value } ) ) }
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
					<Placeholder label={ __( 'JW Player' ) }>
						<p className="components-placeholder__error">{ url }</p>
						<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
					</Placeholder>
				),
				! url && (
					<Placeholder label={ __( 'JW Player' ) }>
						<p>{ __( 'Add required data to use the block.' ) }</p>
					</Placeholder>
				)
			];
		},

		save( { attributes } ) {
			let jwProps = {
				layout: attributes.layout,
				height: attributes.height,
				'data-player-id': attributes.dataPlayerId
			};
			if ( 'fixed-height' !== attributes.layout && attributes.width ) {
				jwProps.width = attributes.width;
			}
			if ( attributes.dataPlaylistId ) {
				jwProps[ 'data-playlist-id' ] = attributes.dataPlaylistId;
			} else if ( attributes.dataMediaId ) {
				jwProps[ 'data-media-id' ] = attributes.dataMediaId;
			}
			return (
				<amp-jwplayer { ...jwProps }></amp-jwplayer>
			);
		}
	}
);
