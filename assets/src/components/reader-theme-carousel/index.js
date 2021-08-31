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
	 * @property {string}  availability    - Availability.
	 * @property {string}  screenshot_url  - Screenshot URL.
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
					__( 'Choose the theme to be used for AMP pages. This theme will normally be exclusively shown to mobile visitors.', 'amp' )
				}
			</p>
			{ (
				currentTheme && currentTheme.is_reader_theme && (
					<AMPNotice>
						<p>
							{
								sprintf(
									/* translators: placeholder is the name of a WordPress theme. */
									__( 'Your active theme “%s” is not listed below because it is already AMP-compatible. If you wish to use your active theme for both AMP and non-AMP pages, then Transitional template mode is what you should choose.', 'amp' ),
									currentTheme.name,
								)
							}
						</p>
					</AMPNotice>
				)
			) }
			<ThemesAPIError />
			<div>
				{
					hasUnavailableThemes && (
						<AMPNotice type={ NOTICE_TYPE_INFO }>
							<p>
								{ __( 'Some supported themes cannot be installed automatically on this site. To use, please install them manually or contact your hosting provider.', 'amp' ) }
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
