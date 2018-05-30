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
	'amp/amp-o2-player',
	{
		title: __( 'AMP O2 Player', 'amp' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' ),
			__( 'AOL O2Player', 'amp' )
		],

		// @todo Add other useful macro toggles, e.g. showing relevant content.
		attributes: {
			dataPid: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'data-pid'
			},
			dataVid: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'data-vid'
			},
			dataBcid: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'data-bcid'
			},
			dataBid: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'data-bid'
			},
			autoPlay: {
				type: 'boolean',
				default: false
			},
			layout: {
				type: 'string',
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-o2-player',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, isSelected, setAttributes } = props;
			const { autoPlay, dataPid, dataVid, dataBcid, dataBid } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
				{ value: 'nodisplay', label: __( 'No Display', 'amp' ) }

			];
			let url = false;
			if ( dataPid && ( dataBcid || dataVid ) ) {
				url = `https://delivery.vidible.tv/htmlembed/pid=${dataPid}/`;
			}
			return (
				<Fragment>
					{
						isSelected && (
							<InspectorControls key='inspector'>
								<PanelBody title={ __( 'O2 Player Settings', 'amp' ) }>
									<TextControl
										label={ __( 'Player ID (required)', 'amp' ) }
										value={ dataPid }
										onChange={ value => ( setAttributes( { dataPid: value } ) ) }
									/>
									<TextControl
										label={ __( 'Buyer Company ID (either buyer or video ID is required)', 'amp' ) }
										value={ dataBcid }
										onChange={ value => ( setAttributes( { dataBcid: value } ) ) }
									/>
									<TextControl
										label={ __( 'Video ID (either buyer or video ID is required)', 'amp' ) }
										value={ dataVid }
										onChange={ value => ( setAttributes( { dataVid: value } ) ) }
									/>
									<TextControl
										label={ __( 'Playlist ID', 'amp' ) }
										value={ dataBid }
										onChange={ value => ( setAttributes( { dataBid: value } ) ) }
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
						)
					}
					{
						url && getMediaPlaceholder( __( 'O2 Player', 'amp' ), url )
					}
					{
						! url && (
							<Placeholder label={ __( 'O2 Player', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
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
