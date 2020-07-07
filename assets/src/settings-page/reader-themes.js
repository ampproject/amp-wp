/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemeSelection } from '../components/reader-theme-selection';

/**
 * The reader themes section of the settings page.
 */
export function ReaderThemes() {
	const { editedOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions;

	if ( 'reader' !== themeSupport ) {
		return null;
	}

	return (
		<section className="reader-themes">
			<h2>
				{ __( 'Choose Reader Theme', 'amp' ) }
			</h2>
			<ReaderThemeSelection disableCurrentlyActiveTheme={ true } currentlyActiveThemeNotice={ __( 'This is the active theme on your site. We recommend transitional mode.', 'amp' ) } />
		</section>
	);
}
