/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { AMP_QUERY_VAR, DEFAULT_AMP_QUERY_VAR, LEGACY_THEME_SLUG, AMP_QUERY_VAR_CUSTOMIZED_LATE } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useMemo, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { ReaderThemes } from '../reader-themes-context-provider';
import { Loading } from '../loading';
import './style.css';
import { AMPNotice, NOTICE_TYPE_WARNING } from '../amp-notice';
import { ThemeCard } from '../theme-card';
import { Carousel } from '../carousel';

/**
 * Component for selecting a reader theme.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.hideCurrentlyActiveTheme Whether the currently active theme should be unselectable.
 */
export function ReaderThemeCarousel( { hideCurrentlyActiveTheme = false } ) {
	const { currentTheme, fetchingThemes, selectedTheme, themes: unprocessedThemes } = useContext( ReaderThemes );

	const [ includeUnavailableThemes, setIncludeUnavailableThemes ] = useState( false );

	const { activeTheme, themes } = useMemo( () => {
		let active, processedThemes;

		if ( hideCurrentlyActiveTheme ) {
			processedThemes = ( unprocessedThemes || [] ).filter( ( theme ) => {
				if ( 'active' === theme.availability ) {
					active = theme;
					return false;
				}
				return true;
			} );
		} else {
			active = null;
			processedThemes = unprocessedThemes;
		}

		return { activeTheme: active, themes: processedThemes };
	}, [ hideCurrentlyActiveTheme, unprocessedThemes ] );

	// Separate available themes (both installed and installable) from those that need to be installed manually.
	const { hasUnavailableThemes, shownThemes } = useMemo(
		() => ( themes || [] )
			.filter( ( theme ) => ! ( hideCurrentlyActiveTheme && currentTheme.name === theme.name ) )
			.reduce(
				( collection, theme ) => {
					if ( ( AMP_QUERY_VAR_CUSTOMIZED_LATE && theme.slug !== LEGACY_THEME_SLUG ) || theme.availability === 'non-installable' ) {
						collection.hasUnavailableThemes = true;
						if ( includeUnavailableThemes ) {
							collection.shownThemes.push( theme );
						}
					} else {
						collection.shownThemes.push( theme );
					}

					return collection;
				},
				{ shownThemes: [], hasUnavailableThemes: false },
			),
		[ currentTheme.name, hideCurrentlyActiveTheme, includeUnavailableThemes, themes ],
	);

	// Memoize carousel items to avoid flickering images on every render.
	const carouselItems = useMemo(
		() => shownThemes
			.map( ( theme ) => (
				{
					label: theme.name,
					name: theme.slug,
					Item: () => (
						<ThemeCard
							disabled={ 'non-installable' === theme.availability }
							ElementName="div"
							screenshotUrl={ theme.screenshot_url }
							{ ...theme }
							aria-label={ theme.name }
							id={ `theme-card-${ theme.slug }` }
						/>
					),
				} ) ),
		[ shownThemes ],
	);

	if ( fetchingThemes ) {
		return <Loading />;
	}

	return (
		<div className="reader-theme-selection">
			<p>
				{
					// @todo Probably improve this text.
					__( 'Select the theme template for mobile visitors', 'amp' )
				}
			</p>
			{ activeTheme && hideCurrentlyActiveTheme && (
				<AMPNotice>
					<p>
						{
							sprintf(
								/* translators: placeholder is the name of a WordPress theme. */
								__( 'Your active theme “%s” is not available as a reader theme. If you wish to use it, Transitional mode may be the best option for you.', 'amp' ),
								activeTheme.name,
							)
						}
					</p>
				</AMPNotice>
			) }
			<div>
				{ 0 < shownThemes.length && (
					<Carousel
						items={ carouselItems }
						highlightedItemIndex={ shownThemes.findIndex( ( { name } ) => name === selectedTheme.name ) }
					/>
				) }

				{
					hasUnavailableThemes && (
						<AMPNotice type={ NOTICE_TYPE_WARNING }>
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
									: __( 'Some supported themes cannot be installed automatically on this site. To use, please install them manually or contact your hosting provider', 'amp' )
								}
							</p>
							<CheckboxControl
								label={ __( 'Show unavailable themes', 'amp' ) }
								onChange={ ( checked ) => {
									setIncludeUnavailableThemes( checked );
								} }
								checked={ includeUnavailableThemes }
							/>
						</AMPNotice>
					)
				}
			</div>
		</div>
	);
}

ReaderThemeCarousel.propTypes = {
	hideCurrentlyActiveTheme: PropTypes.bool,
};
