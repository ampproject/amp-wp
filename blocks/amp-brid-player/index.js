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
	Placeholder,
	ToggleControl
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-brid-player',
	{
		title: __( 'AMP Brid Player', 'amp' ),
		description: __( 'Displays the Brid Player used in Brid.tv Video Platform.', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		attributes: {
			autoPlay: {
				type: 'boolean'
			},
			dataPartner: {
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'data-partner'
			},
			dataPlayer: {
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'data-player'
			},
			dataVideo: {
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'data-video'
			},
			dataPlaylist: {
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'data-playlist'
			},
			dataOutstream: {
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'data-outstream'
			},
			ampLayout: {
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600
			},
			height: {
				default: 400,
				source: 'attribute',
				selector: 'amp-brid-player',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { autoPlay, dataPartner, dataPlayer, dataVideo, dataPlaylist, dataOutstream } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
				{ value: 'nodisplay', label: __( 'No Display', 'amp' ) }

			];
			let url = false;
			if ( dataPartner && dataPlayer && ( dataVideo || dataPlaylist || dataOutstream ) ) {
				url = `http://cdn.brid.tv/live/partners/${dataPartner}`;
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'Brid Player Settings', 'amp' ) }>
							<TextControl
								label={ __( 'Brid.tv partner ID (required)', 'amp' ) }
								value={ dataPartner }
								onChange={ value => ( setAttributes( { dataPartner: value } ) ) }
							/>
							<TextControl
								label={ __( 'Brid.tv player ID (required)', 'amp' ) }
								value={ dataPlayer }
								onChange={ value => ( setAttributes( { dataPlayer: value } ) ) }
							/>
							<TextControl
								label={ __( 'Video ID (one of video / playlist / outstream ID is required)', 'amp' ) }
								value={ dataVideo }
								onChange={ value => ( setAttributes( { dataVideo: value } ) ) }
							/>
							<TextControl
								label={ __( 'Outstream unit ID (one of video / playlist / outstream ID is required)', 'amp' ) }
								value={ dataOutstream }
								onChange={ value => ( setAttributes( { dataOutstream: value } ) ) }
							/>
							<TextControl
								label={ __( 'Playlist ID (one of video / playlist / outstream ID is required)', 'amp' ) }
								value={ dataPlaylist }
								onChange={ value => ( setAttributes( { dataPlaylist: value } ) ) }
							/>
							<ToggleControl
								label={ __( 'Autoplay', 'amp' ) }
								checked={ autoPlay }
								onChange={ () => ( setAttributes( { autoPlay: ! autoPlay } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						url && getMediaPlaceholder( __( 'Brid Player', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'Brid Player', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let bridProps = {
				layout: attributes.ampLayout,
				height: attributes.height,
				'data-player': attributes.dataPlayer,
				'data-partner': attributes.dataPartner
			};
			if ( 'fixed-height' !== attributes.ampLayout && attributes.width ) {
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
