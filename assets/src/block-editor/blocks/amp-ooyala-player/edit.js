/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Placeholder,
	SelectControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls, MediaPlaceholder } from '../../components';

const BlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { dataEmbedCode, dataPlayerId, dataPcode, dataPlayerVersion } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
		{ value: 'fill', label: __( 'Fill', 'amp' ) },
		{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },

	];
	let url = false;
	if ( dataEmbedCode && dataPlayerId && dataPcode ) {
		url = `http://cf.c.ooyala.com/${ dataEmbedCode }`;
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Ooyala settings', 'amp' ) }>
					<TextControl
						label={ __( 'Video embed code (required)', 'amp' ) }
						value={ dataEmbedCode }
						onChange={ ( value ) => ( setAttributes( { dataEmbedCode: value } ) ) }
					/>
					<TextControl
						label={ __( 'Player ID (required)', 'amp' ) }
						value={ dataPlayerId }
						onChange={ ( value ) => ( setAttributes( { dataPlayerId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Provider code for the account (required)', 'amp' ) }
						value={ dataPcode }
						onChange={ ( value ) => ( setAttributes( { dataPcode: value } ) ) }
					/>
					<SelectControl
						label={ __( 'Player version', 'amp' ) }
						value={ dataPlayerVersion }
						options={ [
							{ value: 'v3', label: __( 'V3', 'amp' ) },
							{ value: 'v4', label: __( 'V4', 'amp' ) },
						] }
						onChange={ ( value ) => ( setAttributes( { dataPlayerVersion: value } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ url && <MediaPlaceholder name={ __( 'Ooyala Player', 'amp' ) } url={ url } /> }
			{ ! url && (
				<Placeholder label={ __( 'Ooyala Player', 'amp' ) }>
					<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
				</Placeholder>
			) }
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataEmbedCode: PropTypes.string,
		dataPlayerId: PropTypes.string,
		dataPcode: PropTypes.string,
		dataPlayerVersion: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
