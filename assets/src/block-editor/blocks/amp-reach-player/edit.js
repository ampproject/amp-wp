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
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		dataEmbedId: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;
