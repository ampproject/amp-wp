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
	SelectControl,
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-springboard-player',
	{
		title: __( 'AMP Springboard Player', 'amp' ),
		description: __( 'Displays the Springboard Player used in the Springboard Video Platform', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		attributes: {
			dataSiteId: {
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-site-id'
			},
			dataContentId: {
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-content-id'
			},
			dataPlayerId: {
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-player-id'
			},
			dataDomain: {
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-domain'
			},
			dataMode: {
				default: 'video',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-mode'
			},
			dataItems: {
				default: 1,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'data-items'
			},
			ampLayout: {
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'layout'
			},
			width: {
				default: 600,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'width'
			},
			height: {
				default: 400,
				source: 'attribute',
				selector: 'amp-springboard-player',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) }

			];
			let url = false;
			if ( dataSiteId && dataContentId && dataDomain && dataMode && dataItems ) {
				url = 'https://cms.springboardplatform.com/embed_iframe/';
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Springboard Player Settings', 'amp' ) }>
							<TextControl
								label={ __( 'SprintBoard site ID (required)', 'amp' ) }
								value={ dataSiteId }
								onChange={ value => ( setAttributes( { dataSiteId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Player content ID (required)', 'amp' ) }
								value={ dataContentId }
								onChange={ value => ( setAttributes( { dataContentId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Player ID', 'amp' ) }
								value={ dataPlayerId }
								onChange={ value => ( setAttributes( { dataPlayerId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Springboard partner domain', 'amp' ) }
								value={ dataDomain }
								onChange={ value => ( setAttributes( { dataDomain: value } ) ) }
							/>
							<SelectControl
								label={ __( 'Mode (required)', 'amp' ) }
								value={ dataMode }
								options={ [
									{ value: 'video', label: __( 'Video', 'amp' ) },
									{ value: 'playlist', label: __( 'Playlist', 'amp' ) }
								] }
								onChange={ value => ( setAttributes( { dataMode: value } ) ) }
							/>
							<TextControl
								type="number"
								label={ __( 'Number of video is playlist (required)', 'amp' ) }
								value={ dataItems }
								onChange={ value => ( setAttributes( { dataItems: value } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'Springboard Player', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'Springboard Player', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems, ampLayout, height, width } = attributes;
			let springboardProps = {
				layout: ampLayout,
				height: height,
				'data-site-id': dataSiteId,
				'data-mode': dataMode,
				'data-content-id': dataContentId,
				'data-player-id': dataPlayerId,
				'data-domain': dataDomain,
				'data-items': dataItems
			};
			if ( 'fixed-height' !== ampLayout && width ) {
				springboardProps.width = attributes.width;
			}
			return (
				<amp-springboard-player { ...springboardProps }></amp-springboard-player>
			);
		}
	}
);
