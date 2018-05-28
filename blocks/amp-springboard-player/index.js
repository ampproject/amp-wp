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
				type: 'string',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-side-id'
			},
			dataContentId: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-content-id'
			},
			dataPlayerId: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-player-id'
			},
			dataDomain: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-domain'
			},
			dataMode: {
				type: 'string',
				default: 'video',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-mode'
			},
			dataItems: {
				type: 'number',
				default: 1,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-items'
			},
			layout: {
				type: 'string',
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'height'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) }

			];
			let url = false;
			if ( dataSiteId && dataContentId && dataDomain && dataMode && dataItems ) {
				url = 'https://cms.springboardplatform.com/embed_iframe/';
			}
			return (
				<Fragment>
					{
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
						)
					}
					{
						url && (
							<Placeholder label={ __( 'Springboard Player' ) }>
								<p className="components-placeholder__error">{ url }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
							</Placeholder>
						)
					}
					{
						! url && (
							<Placeholder label={ __( 'Springboard Player' ) }>
								<p>{ __( 'Add required data to use the block.' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
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
