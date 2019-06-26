/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Placeholder, TextControl, ToggleControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls, MediaPlaceholder } from '../../components';

const BlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { dataDelayAdRequest, dataTag, dataSrc, dataPoster } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },

	];
	let dataSet = false;
	if ( dataTag && dataSrc ) {
		dataSet = true;
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'IMA Video Settings', 'amp' ) }>
					<TextControl
						label={ __( 'HTTPS URL for your VAST ad document (required)', 'amp' ) }
						value={ dataTag }
						onChange={ ( value ) => ( setAttributes( { dataTag: value } ) ) }
					/>
					<TextControl
						label={ __( 'HTTPS URL of your video content (required)', 'amp' ) }
						value={ dataSrc }
						onChange={ ( value ) => ( setAttributes( { dataSrc: value } ) ) }
					/>
					<TextControl
						label={ __( 'HTTPS URL to preview image', 'amp' ) }
						value={ dataPoster }
						onChange={ ( value ) => ( setAttributes( { dataPoster: value } ) ) }
					/>
					<ToggleControl
						label={ __( 'Delay Ad Request', 'amp' ) }
						checked={ dataDelayAdRequest }
						onChange={ () => ( setAttributes( { dataDelayAdRequest: ! dataDelayAdRequest } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ dataSet && <MediaPlaceholder name={ __( 'IMA Video', 'amp' ) } url={ dataSrc } /> }
			{
				! dataSet && (
					<Placeholder label={ __( 'IMA Video', 'amp' ) }>
						<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
					</Placeholder>
				)
			}
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataSrc: PropTypes.string,
		dataTag: PropTypes.string,
		dataDelayAdRequest: PropTypes.string,
		dataPoster: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
