/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { getActiveFormat, applyFormat, removeFormat } from '@wordpress/rich-text';

/**
 * Internal dependencies
 */
import { name } from './';

const FormatEdit = ( { isActive, value, onChange } ) => {
	let activeColor;

	if ( isActive ) {
		const activeFormat = getActiveFormat( value, name );
		activeColor = activeFormat.attributes.style;
	}

	return (
		<InspectorControls>
			<PanelColorSettings
				title={ __( 'Inline Background Color', 'amp' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: activeColor,
						onChange: ( color ) => {
							if ( color ) {
								onChange( applyFormat( value, {
									type: name,
									attributes: {
										'data-text-background-color': color,
									},
								} ) );

								return;
							}

							onChange( removeFormat( value, name ) );
						},
						label: __( 'Apply color to the selected text.', 'amp' ),
					},
				] }
			/>
		</InspectorControls>
	);
};

FormatEdit.propTypes = {
	isActive: PropTypes.bool.isRequired,
	value: PropTypes.string,
	onChange: PropTypes.func.isRequired,
};

export default FormatEdit;
