/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Placeholder, TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls, MediaPlaceholder } from '../../components';

const BlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { dataPlayerId, dataMediaId, dataPlaylistId } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed-height', label: __( 'Fixed Height', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
		{ value: 'fill', label: __( 'Fill', 'amp' ) },
		{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },
		{ value: 'nodisplay', label: __( 'No Display', 'amp' ) },

	];
	let url = false;
	if ( dataPlayerId && ( dataMediaId || dataPlaylistId ) ) {
		if ( dataPlaylistId ) {
			url = `https://content.jwplatform.com/players/${ dataPlaylistId }-${ dataPlayerId }`;
		} else {
			url = `https://content.jwplatform.com/players/${ dataMediaId }-${ dataPlayerId }`;
		}
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'JW Player Settings', 'amp' ) }>
					<TextControl
						label={ __( 'Player ID (required)', 'amp' ) }
						value={ dataPlayerId }
						onChange={ ( value ) => ( setAttributes( { dataPlayerId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Media ID (required if playlist ID not set)', 'amp' ) }
						value={ dataMediaId }
						onChange={ ( value ) => ( setAttributes( { dataMediaId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Playlist ID (required if media ID not set)', 'amp' ) }
						value={ dataPlaylistId }
						onChange={ ( value ) => ( setAttributes( { dataPlaylistId: value } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ url && <MediaPlaceholder name={ __( 'JW Player', 'amp' ) } url={ url } /> }
			{
				! url && (
					<Placeholder label={ __( 'JW Player', 'amp' ) }>
						<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
					</Placeholder>
				)
			}
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataPlayerId: PropTypes.string,
		dataMediaId: PropTypes.string,
		dataPlaylistId: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
