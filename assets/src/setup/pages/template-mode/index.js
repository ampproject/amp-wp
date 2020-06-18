/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';

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
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options, updateOptions } = useContext( Options );
	const { developerToolsOption } = useContext( User );
	const { pluginIssues, themeIssues, scanningSite } = useContext( SiteScan );

	const { theme_support: themeSupport } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( false === canGoForward && false === scanningSite && themeSupport ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward, scanningSite, themeSupport ] );

	if ( scanningSite || null === developerToolsOption ) {
		return <Loading />;
	}

	// The actual display component should avoid using global context directly. This will facilitate developing and testing the UI using different options..
	return (
		<ScreenUI
			currentMode={ themeSupport }
			developerToolsOption={ developerToolsOption }
			pluginIssues={ pluginIssues }
			setCurrentMode={ ( mode ) => {
				updateOptions( { theme_support: mode } );
			} }
			themeIssues={ themeIssues }
		/>
	);
}
