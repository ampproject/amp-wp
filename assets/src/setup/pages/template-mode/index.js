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
import { Selections } from './selections';

/**
 * Screen for selecting the template mode.
 */
export function TemplateMode() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options, updateOptions } = useContext( Options );
	const { recommendedModes, scanningSite } = useContext( SiteScan );

	const { theme_support: themeSupport } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( false === canGoForward && false === scanningSite && themeSupport ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward, scanningSite, themeSupport ] );

	if ( scanningSite ) {
		return <Loading />;
	}

	return (
		<Selections
			recommendedModes={ recommendedModes }
			currentMode={ themeSupport }
			setCurrentMode={ ( mode ) => {
				updateOptions( { theme_support: mode } );
			} }
		/>
	);
}
