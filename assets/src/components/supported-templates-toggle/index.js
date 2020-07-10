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

export function SupportedTemplatesToggle() {
	const { editedOptions, updateOptions } = useContext( Options );

	const { all_templates_supported: allTemplatesSupported, reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	const isReaderMode = 'reader' === themeSupport;
	const isLegacy = isReaderMode && 'legacy' === readerTheme;

	return (
		<>
			{ isLegacy && (
				<AMPNotice>
					<p>
						{ __( 'This setting is not available when the legacy Reader theme is selected.', 'amp' ) }
					</p>
				</AMPNotice>
			) }
			<AMPSettingToggle
				checked={ true === allTemplatesSupported }
				disabled={ isLegacy }
				text={ __( 'This will allow all of the URLs on your site to be served as AMP by default.', 'amp' ) }
				title={ __( 'Serve all templates as AMP regardless of what is being queried.', 'amp' ) }
				onChange={ () => {
					updateOptions( { all_templates_supported: ! allTemplatesSupported } );
				} }
			/>
		</>
	);
}
