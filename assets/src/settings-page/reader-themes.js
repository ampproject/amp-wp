/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Panel, PanelBody } from '@wordpress/components';
import { Options } from '../components/options-context-provider';
import { ReaderThemeSelection } from '../components/reader-theme-selection';
import { ReaderThemes as ReaderThemesContext } from '../components/reader-themes-context-provider';

/**
 * The reader themes section of the settings page.
 */
export function ReaderThemes() {
	const { editedOptions } = useContext( Options );
	const { selectedTheme } = useContext( ReaderThemesContext );

	const { theme_support: themeSupport } = editedOptions;

	if ( 'reader' !== themeSupport ) {
		return null;
	}

	return (
		<Panel className="reader-themes">
			<PanelBody
				title={
					selectedTheme ? (
						<>
							{ __( 'Chosen Reader Theme:', 'amp' ) }
							<span className="reader-themes__current-theme">
								{ selectedTheme.name }
							</span>
						</>
					) : __( 'Choose Reader Theme', 'amp' )
				}
				initialOpen={ false }>
				<ReaderThemeSelection hideCurrentlyActiveTheme={ true } />
			</PanelBody>
		</Panel>
	);
}
