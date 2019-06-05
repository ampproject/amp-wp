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
	const { dataSiteId, dataPlayerId, dataContentId, dataDomain, dataMode, dataItems } = attributes;
	const ampLayoutOptions = [
		{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
		{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
		{ value: 'fill', label: __( 'Fill', 'amp' ) },
		{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) },

	];
	let url = false;
	if ( dataSiteId && dataContentId && dataDomain && dataMode && dataItems ) {
		url = 'https://cms.springboardplatform.com/embed_iframe/';
	}
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Springboard Player Settings', 'amp' ) }>
					<TextControl
						label={ __( 'SprintBoard site ID (required)', 'amp' ) }
						value={ dataSiteId }
						onChange={ ( value ) => ( setAttributes( { dataSiteId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Player content ID (required)', 'amp' ) }
						value={ dataContentId }
						onChange={ ( value ) => ( setAttributes( { dataContentId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Player ID', 'amp' ) }
						value={ dataPlayerId }
						onChange={ ( value ) => ( setAttributes( { dataPlayerId: value } ) ) }
					/>
					<TextControl
						label={ __( 'Springboard partner domain', 'amp' ) }
						value={ dataDomain }
						onChange={ ( value ) => ( setAttributes( { dataDomain: value } ) ) }
					/>
					<SelectControl
						label={ __( 'Mode (required)', 'amp' ) }
						value={ dataMode }
						options={ [
							{ value: 'video', label: __( 'Video', 'amp' ) },
							{ value: 'playlist', label: __( 'Playlist', 'amp' ) },
						] }
						onChange={ ( value ) => ( setAttributes( { dataMode: value } ) ) }
					/>
					<TextControl
						type="number"
						label={ __( 'Number of video is playlist (required)', 'amp' ) }
						value={ dataItems }
						onChange={ ( value ) => ( setAttributes( { dataItems: value } ) ) }
					/>
					<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
				</PanelBody>
			</InspectorControls>
			{ url && <MediaPlaceholder name={ __( 'Springboard Player', 'amp' ) } url={ url } /> }
			{
				! url && (
					<Placeholder label={ __( 'Springboard Player', 'amp' ) }>
						<p>{ __( 'Add required data to use the block.', 'amp' ) }</p>
					</Placeholder>
				)
			}
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataSiteId: PropTypes.string,
		dataPlayerId: PropTypes.string,
		dataContentId: PropTypes.string,
		dataDomain: PropTypes.string,
		dataMode: PropTypes.oneOf( [ 'video', 'playlist' ] ),
		dataItems: PropTypes.number,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
