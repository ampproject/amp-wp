/**
 * WordPress dependencies
 */
import { useEffect, useContext, useMemo } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { SiteScan } from '../../components/site-scan-context-provider';
import { ReaderThemes } from '../../components/reader-themes-context-provider';
import { Loading } from '../../components/loading';
import { User } from '../../components/user-context-provider';
import { ScreenUI } from './screen-ui';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
	const { setCanGoForward } = useContext( Navigation );
	const { editedOptions, originalOptions, updates, updateOptions } = useContext( Options );
	const { developerToolsOption, originalDeveloperToolsOption } = useContext( User );
	const { pluginIssues, themeIssues, scanningSite } = useContext( SiteScan );
	const { currentTheme, themes } = useContext( ReaderThemes );

	const technicalQuestionChanged = developerToolsOption !== originalDeveloperToolsOption;

	/**
	 * The prechecked option on the screen depends on how the user answered the technical question.
	 */
	const themeSupport = useMemo( () => {
		// If the user has previously edited the option in this session, persist it.
		if ( editedOptions.theme_support !== originalOptions.theme_support ) {
			return editedOptions.theme_support;
		}

		// If the technical question was set to something different than it was previously, return the updated option
		// or null/undefined if the user hasn't made a selection yet.
		if ( technicalQuestionChanged ) {
			return updates.theme_support;
		}

		// Otherwise return the option currently in state, whether it has been edited in this session or not.
		return editedOptions.theme_support;
	}, [ editedOptions.theme_support, originalOptions.theme_support, technicalQuestionChanged, updates.theme_support ] );

	const currentThemeIsReaderTheme = useMemo( () => {
		return Boolean( themes.find( ( { name } ) => name === currentTheme.name ) );
	}, [ currentTheme, themes ] );

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
			currentThemeIsReaderTheme={ currentThemeIsReaderTheme }
			developerToolsOption={ developerToolsOption }
			firstTimeInWizard={ false === originalOptions.wizard_completed }
			pluginIssues={ pluginIssues }
			savedCurrentMode={ originalOptions.theme_support }
			setCurrentMode={ ( mode ) => {
				updateOptions( { theme_support: mode } );
			} }
			technicalQuestionChanged={ technicalQuestionChanged }
			themeIssues={ themeIssues }
		/>
	);
}
