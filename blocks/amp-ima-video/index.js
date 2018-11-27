/**
 * This file is part of the AMP Plugin for WordPress.
 *
 * The AMP Plugin for WordPress is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2
 * of the License, or (at your option) any later version.
 *
 * The AMP Plugin for WordPress is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the AMP Plugin for WordPress. If not, see <https://www.gnu.org/licenses/>.
 */

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
	'amp/amp-ima-video',
	{
		title: __( 'AMP IMA Video', 'amp' ),
		description: __( 'Embeds a video player for instream video ads that are integrated with the IMA SDK', 'amp' ),
		category: 'embed',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' )
		],

		// @todo Perhaps later add subtitles option and additional source options?
		attributes: {
			dataDelayAdRequest: {
				default: false,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-delay-ad-request'
			},
			dataTag: {
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-tag'
			},
			dataSrc: {
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-src'
			},
			dataPoster: {
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'data-poster'
			},
			ampLayout: {
				default: 'responsive',
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'layout'
			},
			width: {
				default: 600,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'width'
			},
			height: {
				default: 400,
				source: 'attribute',
				selector: 'amp-ima-video',
				attribute: 'height'
			}
		},

		edit( props ) {
			const { attributes, setAttributes } = props;
			const { dataDelayAdRequest, dataTag, dataSrc, dataPoster } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) }

			];
			let dataSet = false;
			if ( dataTag && dataSrc ) {
				dataSet = true;
			}
			return (
				<Fragment>
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'IMA Video Settings', 'amp' ) }>
							<TextControl
								label={ __( 'Https URL for your VAST ad document (required)', 'amp' ) }
								value={ dataTag }
								onChange={ value => ( setAttributes( { dataTag: value } ) ) }
							/>
							<TextControl
								label={ __( 'Https URL of your video content (required)', 'amp' ) }
								value={ dataSrc }
								onChange={ value => ( setAttributes( { dataSrc: value } ) ) }
							/>
							<TextControl
								label={ __( 'Https URL to preview image', 'amp' ) }
								value={ dataPoster }
								onChange={ value => ( setAttributes( { dataPoster: value } ) ) }
							/>
							<ToggleControl
								label={ __( 'Delay Ad Request', 'amp' ) }
								checked={ dataDelayAdRequest }
								onChange={ () => ( setAttributes( { dataDelayAdRequest: ! dataDelayAdRequest } ) ) }
							/>
							{
								getLayoutControls( props, ampLayoutOptions )
							}
						</PanelBody>
					</InspectorControls>
					{
						dataSet && getMediaPlaceholder( __( 'IMA Video', 'amp' ), dataSrc )
					}
					{
						! dataSet && (
							<Placeholder label={ __( 'IMA Video', 'amp' ) }>
								<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			let imaProps = {
				layout: attributes.ampLayout,
				height: attributes.height,
				width: attributes.width,
				'data-tag': attributes.dataTag,
				'data-src': attributes.dataSrc
			};
			if ( attributes.dataPoster ) {
				imaProps[ 'data-poster' ] = attributes.dataPoster;
			}
			if ( attributes.dataDelayAdRequest ) {
				imaProps[ 'data-delay-ad-request' ] = attributes.dataDelayAdRequest;
			}
			return (
				<amp-ima-video { ...imaProps }></amp-ima-video>
			);
		}
	}
);
