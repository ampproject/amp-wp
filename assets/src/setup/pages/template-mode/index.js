/**
 * WordPress dependencies
 */
import { useEffect, useContext, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { SiteScan } from '../../components/site-scan-context-provider';
import { Loading } from '../../components/loading';
import { User } from '../../components/user-context-provider';
import { ScreenUI } from './screen-ui';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
	// Initialize theme support setting as empty to force user to make a selection.
	const [ localThemeSupport, setLocalThemeSupport ] = useState( null );

	const { setCanGoForward } = useContext( Navigation );
	const { options, originalOptions, updateOptions } = useContext( Options );
	const { developerToolsOption } = useContext( User );
	const { pluginIssues, themeIssues, scanningSite } = useContext( SiteScan );

	const { theme_support: themeSupport } = options;

	/**
	 * Persist the user selection in the global context.
	 */
	useEffect( () => {
		if ( null === localThemeSupport ) {
			return;
		}

		if ( localThemeSupport === themeSupport ) {
			return;
		}

		updateOptions( { theme_support: localThemeSupport } );
	}, [ localThemeSupport, themeSupport, updateOptions ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( false === scanningSite && localThemeSupport ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward, scanningSite, localThemeSupport ] );

	if ( scanningSite ) {
		return <Loading />;
	}

	// The actual display component should avoid using global context directly. This will facilitate developing and testing the UI using different options.
	return (
		<ScreenUI
			currentMode={ localThemeSupport }
			developerToolsOption={ developerToolsOption }
			pluginIssues={ pluginIssues }
			savedCurrentMode={ originalOptions.theme_support }
			setCurrentMode={ ( mode ) => {
				setLocalThemeSupport( mode );
			} }
			themeIssues={ themeIssues }
		/>
	);
}
