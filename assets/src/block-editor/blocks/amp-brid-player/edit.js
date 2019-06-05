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
	const { autoPlay, dataPartner, dataPlayer, dataVideo, dataPlaylist, dataOutstream } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
		{ value: 'fill', label: __( 'Fill', 'amp' ) },
		{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
		{ value: 'nodisplay', label: __( 'No Display', 'amp' ) },

	];
	let url = false;
	if ( dataPartner && dataPlayer && ( dataVideo || dataPlaylist || dataOutstream ) ) {
		url = `http://cdn.brid.tv/live/partners/${ dataPartner }`;
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Brid Player Settings', 'amp' ) }>
					<TextControl
						label={ __( 'Brid.tv partner ID (required)', 'amp' ) }
						value={ dataPartner }
						onChange={ ( value ) => ( setAttributes( { dataPartner: value } ) ) }
					/>
					<TextControl
						label={ __( 'Brid.tv player ID (required)', 'amp' ) }
						value={ dataPlayer }
						onChange={ ( value ) => ( setAttributes( { dataPlayer: value } ) ) }
					/>
					<TextControl
						label={ __( 'Video ID (one of video / playlist / outstream ID is required)', 'amp' ) }
						value={ dataVideo }
						onChange={ ( value ) => ( setAttributes( { dataVideo: value } ) ) }
					/>
					<TextControl
						label={ __( 'Outstream unit ID (one of video / playlist / outstream ID is required)', 'amp' ) }
						value={ dataOutstream }
						onChange={ ( value ) => ( setAttributes( { dataOutstream: value } ) ) }
					/>
					<TextControl
						label={ __( 'Playlist ID (one of video / playlist / outstream ID is required)', 'amp' ) }
						value={ dataPlaylist }
						onChange={ ( value ) => ( setAttributes( { dataPlaylist: value } ) ) }
					/>
					<ToggleControl
						label={ __( 'Autoplay', 'amp' ) }
						checked={ autoPlay }
						onChange={ () => ( setAttributes( { autoPlay: ! autoPlay } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ url && <MediaPlaceholder name={ __( 'Brid Player', 'amp' ) } url={ url } /> }
			{
				! url && (
					<Placeholder label={ __( 'Brid Player', 'amp' ) }>
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
		dataPartner: PropTypes.string,
		dataPlayer: PropTypes.string,
		dataVideo: PropTypes.string,
		dataPlaylist: PropTypes.string,
		dataOutstream: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
