/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { Notice, SelectControl, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Layout controls for AMP blocks' attributes: layout, width, height.
 *
 * @param {Object}   props                  Component props.
 * @param {Object}   props.attributes       Block attributes.
 * @param {Function} props.setAttributes    Callback to update block attributes.
 * @param {Array}    props.ampLayoutOptions Layout options.
 * @return {ReactElement} Controls.
 */
const LayoutControls = ( { attributes, setAttributes, ampLayoutOptions } ) => {
	const { ampLayout, height, width } = attributes;
	const showHeightNotice = ! height && ( 'fixed' === ampLayout || 'fixed-height' === ampLayout );
	const showWidthNotice = ! width && 'fixed' === ampLayout;

	return (
		<>
			<SelectControl
				label={ __( 'Layout', 'amp' ) }
				value={ ampLayout }
				options={ ampLayoutOptions }
				onChange={ ( value ) => ( setAttributes( { ampLayout: value } ) ) }
			/>
			{ showWidthNotice && (
				<Notice status="error" isDismissible={ false }>
					{
						sprintf(
							/* translators: %s is the layout name */
							__( 'Width is required for %s layout', 'amp' ),
							ampLayout,
						)
					}
				</Notice>
			) }
			<TextControl
				type="number"
				label={ __( 'Width (px)', 'amp' ) }
				value={ width !== undefined ? width : '' }
				onChange={ ( value ) => ( setAttributes( { width: value } ) ) }
			/>
			{ showHeightNotice && (
				<Notice status="error" isDismissible={ false }>
					{
						sprintf(
							/* translators: %s is the layout name */
							__( 'Height is required for %s layout', 'amp' ),
							ampLayout,
						)
					}
				</Notice>
			) }
			<TextControl
				type="number"
				label={ __( 'Height (px)', 'amp' ) }
				value={ height }
				onChange={ ( value ) => ( setAttributes( { height: value } ) ) }
			/>
		</>
	);
};

LayoutControls.propTypes = {
	attributes: PropTypes.shape( {
		ampLayout: PropTypes.string,
		width: PropTypes.number,
		height: PropTypes.number,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	ampLayoutOptions: PropTypes.arrayOf( PropTypes.shape( {
		value: PropTypes.string.isRequired,
		label: PropTypes.string.isRequired,
	} ) ).isRequired,
};

export default LayoutControls;
