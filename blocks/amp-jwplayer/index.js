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
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-jwplayer',
	{
		title: __( 'AMP JW Player', 'amp' ),
		description: __( 'Displays a cloud-hosted JW Player.', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		attributes: {
			dataPlayerId: {
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'data-player-id'
			},
			dataMediaId: {
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'data-media-id'
			},
			dataPlaylistId: {
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'data-playlist-id'
			},
			ampLayout: {
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'layout'
			},
			width: {
				default: 600,
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'width'
			},
			height: {
				default: 400,
				source: 'attribute',
				selector: 'amp-jwplayer',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataPlayerId, dataMediaId, dataPlaylistId } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
				{ value: 'nodisplay', label: __( 'No Display', 'amp' ) }

			];
			let url = false;
			if ( dataPlayerId && ( dataMediaId || dataPlaylistId ) ) {
				if ( dataPlaylistId ) {
					url = `https://content.jwplatform.com/players/${dataPlaylistId}-${dataPlayerId}`;
				} else {
					url = `https://content.jwplatform.com/players/${dataMediaId}-${dataPlayerId}`;
				}
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'JW Player Settings', 'amp' ) }>
							<TextControl
								label={ __( 'Player ID (required)', 'amp' ) }
								value={ dataPlayerId }
								onChange={ value => ( setAttributes( { dataPlayerId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Media ID (required if playlist ID not set)', 'amp' ) }
								value={ dataMediaId }
								onChange={ value => ( setAttributes( { dataMediaId: value } ) ) }
							/>
							<TextControl
								label={ __( 'Playlist ID (required if media ID not set)', 'amp' ) }
								value={ dataPlaylistId }
								onChange={ value => ( setAttributes( { dataPlaylistId: value } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'JW Player', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'JW Player', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let jwProps = {
				layout: attributes.ampLayout,
				height: attributes.height,
				'data-player-id': attributes.dataPlayerId
			};
			if ( 'fixed-height' !== attributes.ampLayout && attributes.width ) {
				jwProps.width = attributes.width;
			}
			if ( attributes.dataPlaylistId ) {
				jwProps[ 'data-playlist-id' ] = attributes.dataPlaylistId;
			}
			if ( attributes.dataMediaId ) {
				jwProps[ 'data-media-id' ] = attributes.dataMediaId;
			}
			return (
				<amp-jwplayer { ...jwProps }></amp-jwplayer>
			);
		}
	}
);
