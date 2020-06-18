/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext } from '@wordpress/element';

/**
 * External dependencies
 */
import { READER_THEMES_REST_ENDPOINT, UPDATES_NONCE } from 'amp-setup'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Loading } from '../../components/loading';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { ReaderThemes, ReaderThemesContextProvider } from '../../components/reader-themes-context-provider';
import { ThemeCard } from './theme-card';

const { ajaxurl: wpAjaxUrl } = global;

/**
 * The actual screen UI with full context.
 */
function Screen() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options } = useContext( Options );
	const { fetchingThemes, themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( themes && readerTheme && canGoForward === false ) {
			if ( themes.map( ( { slug } ) => slug ).includes( readerTheme ) ) {
				setCanGoForward( true );
			}
		}
	}, [ canGoForward, setCanGoForward, readerTheme, themes ] );

	if ( fetchingThemes ) {
		return (
			<Loading />
		);
	}

	return (
		<div className="choose-reader-theme">
			<p>
				{
					// @todo Probably improve this text.
					__( 'Select the theme template for mobile visitors', 'amp' )
				}
			</p>
			<form>
				<ul className="choose-reader-theme__grid">
					{
						themes && themes.map( ( theme ) => (
							<ThemeCard
								key={ `theme-card-${ theme.slug }` }
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/> ),
						)
					}
				</ul>
			</form>
		</div>
	);
}

/**
 * Checks whether the reader theme UI should load before rendering. Allows deferral of fetching themes.
 */
export function ChooseReaderTheme() {
	const { options } = useContext( Options );
	const { canGoForward, setCanGoForward } = useContext( Navigation );

	const { theme_support: themeSupport } = options || {};

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( 'reader' !== themeSupport && ! canGoForward ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward, themeSupport ] );

	if ( 'reader' === themeSupport ) {
		return (
			<ReaderThemesContextProvider
				wpAjaxUrl={ wpAjaxUrl }
				readerThemesEndpoint={ READER_THEMES_REST_ENDPOINT }
				updatesNonce={ UPDATES_NONCE }
			>
				<Screen />
			</ReaderThemesContextProvider>
		);
	}

	return (
		<p>
			{
				// @todo Probably improve this text.
				__( 'This screen is for choosing a reader theme, which is only applicable in reader mode.', 'amp' )
			}
		</p>
	);
}
