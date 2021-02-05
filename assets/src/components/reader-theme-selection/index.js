/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { AMP_QUERY_VAR, DEFAULT_AMP_QUERY_VAR, AMP_QUERY_VAR_CUSTOMIZED_LATE } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ReaderThemes } from '../reader-themes-context-provider';
import { Loading } from '../loading';
import './style.css';
import { ThemeCard } from '../theme-card';
import { AMPNotice } from '../amp-notice';
import { ThemesAPIError } from '../themes-api-error';

/**
 * Component for selecting a reader theme.
 */
export function ReaderThemeSelection() {
	const { availableThemes, currentTheme, fetchingThemes, unavailableThemes } = useContext( ReaderThemes );

	if ( fetchingThemes ) {
		return <Loading />;
	}

	return (
		<div className="reader-theme-selection">
			<p>
				{
					__( 'Select the theme template for mobile visitors', 'amp' )
				}
			</p>
			{ currentTheme && currentTheme.is_reader_theme && (
				<AMPNotice>
					<p>
						{
							sprintf(
								/* translators: placeholder is the name of a WordPress theme. */
								__( 'Your active theme “%s” is not available as a reader theme. If you wish to use it, Transitional mode may be the best option for you.', 'amp' ),
								currentTheme.name,
							)
						}
					</p>
				</AMPNotice>
			) }
			<ThemesAPIError />
			<div>
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
								? (
									<span
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
								)
								: __( 'The following themes are compatible but cannot be installed automatically. Please install them manually, or contact your host if you are not able to do so.', 'amp' )
							}
						</p>
						<ul className="choose-reader-theme__grid">
							{ unavailableThemes.map( ( theme ) => (
								<ThemeCard
									key={ `theme-card-${ theme.slug }` }
									screenshotUrl={ theme.screenshot_url }
									disabled={ true }
									{ ...theme }
								/>
							) ) }
						</ul>
					</div>
				) }
			</div>
		</div>
	);
}

ReaderThemeSelection.propTypes = {
	hideCurrentlyActiveTheme: PropTypes.bool,
};
