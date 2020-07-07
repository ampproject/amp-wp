/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../options-context-provider';
import { AMPSettingToggle } from '../amp-setting-toggle';
import { AMPNotice } from '../amp-notice';

export function SupportedTemplatesToggle( { themeSupportArgs } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const { all_templates_supported: allTemplatesSupported, theme_support: themeSupport } = editedOptions;

	return 'templates_supported' in themeSupportArgs && 'all' === themeSupportArgs.templates_supported
		? (
			<AMPNotice>
				<p>
					{ __( 'The current theme requires all templates support AMP.', 'amp' ) }
				</p>
			</AMPNotice>
		)
		: 'reader' !== themeSupport && (
			<AMPSettingToggle
				checked={ true === allTemplatesSupported }
				text={ __( 'This will allow all of the URLs on your site to be served as AMP by default.', 'amp' ) }
				title={ __( 'Serve all templates as AMP regardless of what is being queried.', 'amp' ) }
				onChange={ () => {
					updateOptions( { all_templates_supported: ! allTemplatesSupported } );
				} }
			/>
		);
}

SupportedTemplatesToggle.propTypes = {
	themeSupportArgs: PropTypes.shape( {
		templates_supported: PropTypes.any,
	} ).isRequired,
};
