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
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-springboard-player',
	{
		title: __( 'AMP Springboard Player' ),
		description: __( 'Displays the Springboard Player used in the Springboard Video Platform' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' )
		],

		attributes: {
			dataSiteId: {
				type: 'string'
			},
			dataContentId: {
				type: 'string'
			},
			dataPlayerId: {
				type: 'string'
			},
			dataDomain: {
				type: 'string'
			},
			dataMode: {
				type: 'string',
				default: 'video'
			},
			dataItems: {
				type: 'number',
				default: 1
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
			const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: 'Responsive' },
				{ value: 'fixed', label: 'Fixed' },
				{ value: 'fill', label: 'Fill' },
				{ value: 'flex-item', label: 'Flex-item' }

			];
			let url = false;
			if ( dataSiteId && dataContentId && dataDomain && dataMode && dataItems ) {
				url = 'https://cms.springboardplatform.com/embed_iframe/';
			}
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Springboard Player Settings' ) }>
							<TextControl
								label={ __( 'SprintBoard site ID (required)' ) }
								value={ dataSiteId }
								onChange={ value => ( setAttributes( { dataSiteId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Player content ID (required)' ) }
								value={ dataContentId }
								onChange={ value => ( setAttributes( { dataContentId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Player ID' ) }
								value={ dataPlayerId }
								onChange={ value => ( setAttributes( { dataPlayerId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Springboard partner domain' ) }
								value={ dataDomain }
								onChange={ value => ( setAttributes( { dataDomain: value } ) ) }
							/>
							<SelectControl
								label={ __( 'Mode (required)' ) }
								value={ dataMode }
								options={ [
									{ value: 'video', label: __( 'Video' ) },
									{ value: 'playlist', label: __( 'Playlist' ) }
								] }
								onChange={ value => ( setAttributes( { dataMode: value } ) ) }
							/>
							<TextControl
								type="number"
								label={ __( 'Number of video is playlist (required)' ) }
								value={ dataItems }
								onChange={ value => ( setAttributes( { dataItems: value } ) ) }
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
					<Placeholder label={ __( 'Springboard Player' ) }>
						<p className="components-placeholder__error"><a href={ url }>{ url }</a></p>
						<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
					</Placeholder>
				),
				! url && (
					<Placeholder label={ __( 'Springboard Player' ) }>
						<p>{ __( 'Add required data to use the block.' ) }</p>
					</Placeholder>
				)
			];
		},

		save( { attributes } ) {
			const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems, layout, height, width } = attributes;
			let springboardProps = {
				layout: layout,
				height: height,
				'data-site-id': dataSiteId,
				'data-mode': dataMode,
				'data-content-id': dataContentId,
				'data-player-id': dataPlayerId,
				'data-domain': dataDomain,
				'data-items': dataItems
			};
			if ( 'fixed-height' !== layout && width ) {
				springboardProps.width = attributes.width;
			}
			return (
				<amp-springboard-player { ...springboardProps }></amp-springboard-player>
			);
		}
	}
);
