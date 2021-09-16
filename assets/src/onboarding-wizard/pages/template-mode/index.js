/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { User } from '../../../components/user-context-provider';
import { Options } from '../../../components/options-context-provider';
import { Loading } from '../../../components/loading';
import { TemplateModeOverride } from '../../components/template-mode-override-context-provider';
import { ScreenUI } from './screen-ui';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
	const { setCanGoForward } = useContext( Navigation );
	const { editedOptions, originalOptions, updateOptions } = useContext( Options );
	const { developerToolsOption } = useContext( User );
	const { pluginIssues, themeIssues, scanningSite } = useContext( SiteScan );
	const { currentTheme } = useContext( ReaderThemes );
	const { technicalQuestionChangedAtLeastOnce } = useContext( TemplateModeOverride );

	const { theme_support: themeSupport } = editedOptions;

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( false === scanningSite && undefined !== themeSupport ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward, scanningSite, themeSupport ] );

	if ( scanningSite ) {
		return <Loading />;
	}

	// The actual display component should avoid using global context directly. This will facilitate developing and testing the UI using different options.
	return (
		<ScreenUI
			currentMode={ themeSupport }
			currentThemeIsAmongReaderThemes={ currentTheme.is_reader_theme }
			developerToolsOption={ developerToolsOption }
			firstTimeInWizard={ false === originalOptions.plugin_configured }
			pluginIssues={ pluginIssues }
			savedCurrentMode={ originalOptions.theme_support }
			setCurrentMode={ ( mode ) => {
				updateOptions( { theme_support: mode } );
			} }
			technicalQuestionChanged={ technicalQuestionChangedAtLeastOnce }
			themeIssues={ themeIssues }
		/>
	);
}
