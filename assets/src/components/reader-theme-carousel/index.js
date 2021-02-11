/**
 * External dependencies
 */
import { AMP_QUERY_VAR, DEFAULT_AMP_QUERY_VAR, AMP_QUERY_VAR_CUSTOMIZED_LATE } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useMemo, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ReaderThemes } from '../reader-themes-context-provider';
import { Loading } from '../loading';
import './style.css';
import { AMPNotice, NOTICE_TYPE_INFO } from '../amp-notice';
import { AMPSettingToggle } from '../amp-setting-toggle';
import { ThemeCard } from '../theme-card';
import { Carousel, DEFAULT_MOBILE_BREAKPOINT } from '../carousel';
import { useWindowWidth } from '../../utils/use-window-width';
import { ThemesAPIError } from '../themes-api-error';

/**
 * Component for selecting a reader theme.
 */
export function ReaderThemeCarousel() {
	const { windowWidth } = useWindowWidth();

	/**
	 * @typedef Theme
	 * @type {Object}
	 * @property {string} availability - Availability.
	 * @property {string} screenshot_url - Screenshot URL.
	 * @property {boolean} is_reader_theme - Is Reader theme.
	 */

	/** @type {Theme[]} themes */
	const { availableThemes, currentTheme, fetchingThemes, selectedTheme, themes, unavailableThemes } = useContext( ReaderThemes );
	const [ includeUnavailableThemes, setIncludeUnavailableThemes ] = useState( false );

	const hasUnavailableThemes = unavailableThemes.length > 0;
	const shownThemes = includeUnavailableThemes ? themes : availableThemes;

	const isMobile = windowWidth < DEFAULT_MOBILE_BREAKPOINT;

	// Memoize carousel items to avoid flickering images on every render.
	const carouselItems = useMemo(
		() => {
			if ( isMobile ) {
				return shownThemes
					.map( ( theme ) => {
						return {
							label: theme.name,
							name: theme.slug,
							Item: () => (
								<ThemeCard
									disabled={ unavailableThemes.includes( theme ) }
									ElementName="div"
									screenshotUrl={ theme.screenshot_url }
									{ ...theme }
								/>
							),
						};
					} );
			}

			const pages = [];
			const newShownThemes = [ ...shownThemes ];

			while ( newShownThemes.length ) {
				pages.push( newShownThemes.splice( 0, 3 ) );
			}

			return pages.map( ( page, index ) => (
				{
					label: sprintf(
						/* translators: Placeholder is a page number. */
						__( 'Page %d' ),
						index,
					),
					name: `carousel-page-${ index }`,
					Item: () => (
						<div className="amp-carousel__page">
							{ page
								.map( ( theme ) => {
									return (
										<ThemeCard
											key={ `theme-card-${ theme.slug }` }
											disabled={ unavailableThemes.includes( theme ) }
											ElementName="div"
											screenshotUrl={ theme.screenshot_url }
											{ ...theme }
										/>

									);
								} ) }
						</div>
					),
				}
			) );
		},
		[ isMobile, shownThemes, unavailableThemes ],
	);

	const highlightedItemIndex = useMemo(
		() => {
			for ( let i = 0; i < shownThemes.length; i += 1 ) {
				const theme = shownThemes[ i ];
				if ( theme.slug === selectedTheme.slug ) {
					if ( isMobile ) {
						return i;
					}

					// Desktop carousel shows groups of three. Highlighted index is the group containing the selected theme.
					return Math.floor( i / 3 );
				}
			}

			return 0;
		},
		[ isMobile, selectedTheme.slug, shownThemes ],
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
				{
					hasUnavailableThemes && (
						<AMPNotice type={ NOTICE_TYPE_INFO }>
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
									: __( 'Some supported themes cannot be installed automatically on this site. To use, please install them manually or contact your hosting provider.', 'amp' )
								}
							</p>
							<AMPSettingToggle
								text={ __( 'Show unavailable themes', 'amp' ) }
								onChange={ ( checked ) => {
									setIncludeUnavailableThemes( checked );
								} }
								checked={ includeUnavailableThemes }
							/>
						</AMPNotice>
					)
				}

				{ 0 < shownThemes.length && (
					<Carousel
						items={ carouselItems }
						highlightedItemIndex={ highlightedItemIndex }
					/>
				) }
			</div>
		</div>
	);
}
