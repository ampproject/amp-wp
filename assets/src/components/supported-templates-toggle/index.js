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

export function SupportedTemplatesToggle() {
	const { editedOptions, updateOptions } = useContext( Options );

	const { all_templates_supported: allTemplatesSupported, reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	const isReaderMode = 'reader' === themeSupport;
	const isLegacy = isReaderMode && 'legacy' === readerTheme;

	if ( isLegacy ) {
		return null;
	}

	return (
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
