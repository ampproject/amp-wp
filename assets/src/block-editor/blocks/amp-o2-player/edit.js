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
	ToggleControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls, MediaPlaceholder } from '../../components';

const BlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { autoPlay, dataPid, dataVid, dataBcid, dataBid } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
		{ value: 'fill', label: __( 'Fill', 'amp' ) },
		{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
		{ value: 'nodisplay', label: __( 'No Display', 'amp' ) },

	];
	let url = false;
	if ( dataPid && ( dataBcid || dataVid ) ) {
		url = `https://delivery.vidible.tv/htmlembed/pid=${ dataPid }/`;
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'O2 Player Settings', 'amp' ) }>
					<TextControl
						label={ __( 'Player ID (required)', 'amp' ) }
						value={ dataPid }
						onChange={ ( value ) => ( setAttributes( { dataPid: value } ) ) }
					/>
					<TextControl
						label={ __( 'Buyer Company ID (either buyer or video ID is required)', 'amp' ) }
						value={ dataBcid }
						onChange={ ( value ) => ( setAttributes( { dataBcid: value } ) ) }
					/>
					<TextControl
						label={ __( 'Video ID (either buyer or video ID is required)', 'amp' ) }
						value={ dataVid }
						onChange={ ( value ) => ( setAttributes( { dataVid: value } ) ) }
					/>
					<TextControl
						label={ __( 'Playlist ID', 'amp' ) }
						value={ dataBid }
						onChange={ ( value ) => ( setAttributes( { dataBid: value } ) ) }
					/>
					<ToggleControl
						label={ __( 'Autoplay', 'amp' ) }
						checked={ autoPlay }
						onChange={ () => ( setAttributes( { autoPlay: ! autoPlay } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ url && <MediaPlaceholder name={ __( 'O2 Player', 'amp' ) } url={ url } /> }
			{
				! url && (
					<Placeholder label={ __( 'O2 Player', 'amp' ) }>
						<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
					</Placeholder>
				)
			}
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		autoPlay: PropTypes.bool,
		dataPidL: PropTypes.string,
		dataVid: PropTypes.string,
		dataBcid: PropTypes.string,
		dataBid: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
