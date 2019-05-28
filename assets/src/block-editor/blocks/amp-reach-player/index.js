/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Placeholder,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls, MediaPlaceholder } from '../../../components';

export const name = 'amp/amp-reach-player';

export const settings = {
	title: __( 'AMP Reach Player', 'amp' ),
	description: __( 'Displays the Reach Player configured in the Beachfront Reach platform.', 'amp' ),
	category: 'embed',
	icon: 'embed-generic',
	keywords: [
		__( 'Embed', 'amp' ),
		__( 'Beachfront Reach video', 'amp' ),
	],

	attributes: {
		dataEmbedId: {
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'data-embed-id',
		},
		ampLayout: {
			default: 'fixed-height',
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'layout',
		},
		width: {
			default: 600,
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'width',
		},
		height: {
			default: 400,
			source: 'attribute',
			selector: 'amp-reach-player',
			attribute: 'height',
		},
	},

	edit( props ) {
		const { attributes, setAttributes } = props;
		const { dataEmbedId } = attributes;
		const ampLayoutOptions = [
			{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
			{ value: 'fixed-height', label: __( 'Fixed Height', 'amp' ) },
			{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
			{ value: 'fill', label: __( 'Fill', 'amp' ) },
			{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },

		];
		let url = false;
		if ( dataEmbedId ) {
			url = 'https://media-cdn.beachfrontreach.com/acct_1/video/';
		}
		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Reach settings', 'amp' ) }>
						<TextControl
							label={ __( 'The Reach player embed id (required)', 'amp' ) }
							value={ dataEmbedId }
							onChange={ ( value ) => ( setAttributes( { dataEmbedId: value } ) ) }
						/>
						<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
					</PanelBody>
				</InspectorControls>
				{ url && <MediaPlaceholder name={ __( 'Reach Player', 'amp' ) } url={ url } /> }
				{
					! url && (
						<Placeholder label={ __( 'Reach Player', 'amp' ) }>
							<p>{ __( 'Add Reach player embed ID to use the block.', 'amp' ) }</p>
						</Placeholder>
					)
				}
			</>
		);
	},

	save( { attributes } ) {
		const { dataEmbedId, ampLayout, height, width } = attributes;

		const reachProps = {
			layout: ampLayout,
			height,
			'data-embed-id': dataEmbedId,
		};
		if ( 'fixed-height' !== ampLayout && width ) {
			reachProps.width = width;
		}
		return (
			<amp-reach-player { ...reachProps }></amp-reach-player>
		);
	},
};
