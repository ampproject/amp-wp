/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useContext, useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Loading } from '../../components/loading';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { ReaderThemes } from '../../components/reader-themes-context-provider';
import { ThemeCard } from './theme-card';

/**
 * External Dependencies.
 */
import { AMP_QUERY_VAR, DEFAULT_AMP_QUERY_VAR, LEGACY_THEME_SLUG, AMP_QUERY_VAR_CUSTOMIZED_LATE } from 'amp-setup'; // From WP inline script.

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { editedOptions } = useContext( Options );
	const { fetchingThemes, themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( 'reader' !== themeSupport ) {
			setCanGoForward( true );
			return;
		}

		if (
			themes &&
			readerTheme &&
			canGoForward === false &&
			! AMP_QUERY_VAR_CUSTOMIZED_LATE
				? themes.map( ( { slug } ) => slug ).includes( readerTheme )
				: readerTheme === LEGACY_THEME_SLUG
		) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward, readerTheme, themes, themeSupport ] );

	// Separate available themes (both installed and installable) from those that need to be installed manually.
	const { availableThemes, unavailableThemes } = useMemo(
		() => themes.reduce(
			( collections, theme ) => {
				if ( ( AMP_QUERY_VAR_CUSTOMIZED_LATE && theme.slug !== LEGACY_THEME_SLUG ) || theme.availability === 'non-installable' ) {
					collections.unavailableThemes.push( theme );
				} else {
					collections.availableThemes.push( theme );
				}

				return collections;
			},
			{ availableThemes: [], unavailableThemes: [] },
		),
		[ themes ] );

	if ( fetchingThemes ) {
		return (
			<Loading />
		);
	}

	if ( 'reader' !== themeSupport ) {
		return (
			<p>
				{ __( 'This screen is only relevant to sites that use Reader mode. Go back if you would like to select Reader mode, or move forward to complete the setup wizard.', 'amp' ) }
			</p>
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
				{ 0 < availableThemes.length && (
					<ul className="choose-reader-theme__grid">
						{ availableThemes.map( ( theme ) => (
							<ThemeCard
								key={ `theme-card-${ theme.slug }` }
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/>
						) ) }
					</ul>
				) }

				{ 0 < unavailableThemes.length && (
					<div className="choose-reader-theme__unavailable">
						<h3>
							{ __( 'Unavailable themes', 'amp' ) }
						</h3>
						<p>
							{ AMP_QUERY_VAR_CUSTOMIZED_LATE
								/* dangerouslySetInnerHTML reason: Injection of code tags. */
								? <span
									dangerouslySetInnerHTML={ {
										__html: sprintf(
											/* translators: 1: customized AMP query var, 2: default query var, 3: the AMP_QUERY_VAR constant name, 4: the amp_query_var filter, 5: the plugins_loaded action */
											__( 'The following themes are not available because your site (probably the active theme) has customized the AMP query var too late (it is set to %1$s as opposed to the default of %2$s). Please make sure that any customizations done by defining the %3$s constant or adding an %4$s filter are done before the %5$s action with priority 8.', 'amp' ),
											`<code>${ AMP_QUERY_VAR }</code>`,
											`<code>${ DEFAULT_AMP_QUERY_VAR }</code>`,
											'<code>AMP_QUERY_VAR</code>',
											'<code>amp_query_var</code>',
											'<code>plugins_loaded</code>',
										),
									} }
								/>
								: __( 'The following themes are compatible but cannot be installed automatically. Please install them manually, or contact your host if you are not able to do so.', 'amp' )
							}
						</p>
						<ul className="choose-reader-theme__grid">
							{ unavailableThemes.map( ( theme ) => (
								<ThemeCard
									key={ `theme-card-${ theme.slug }` }
									screenshotUrl={ theme.screenshot_url }
									unavailable={ true }
									{ ...theme }
								/>
							) ) }
						</ul>
					</div>
				) }
			</form>
		</div>
	);
}
