/**
 * External dependencies
 */
import {
	IS_CORE_THEME,
	THEME_SUPPORT_ARGS,
	THEME_SUPPORTS_READER_MODE,
} from 'amp-settings';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TemplateModeOption } from '../components/template-mode-option';
import { AMPNotice, NOTICE_SIZE_LARGE, NOTICE_TYPE_INFO, NOTICE_SIZE_SMALL, NOTICE_TYPE_WARNING } from '../components/amp-notice';
import { Options } from '../components/options-context-provider';
import { READER, STANDARD, TRANSITIONAL } from '../common/constants';
import { AMPDrawer, HANDLE_TYPE_RIGHT } from '../components/amp-drawer';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { ReaderThemeCarousel } from '../components/reader-theme-carousel';

/**
 * Small notice indicating a mode is recommended.
 */
function RecommendedNotice() {
	return (
		<AMPNotice size={ NOTICE_SIZE_SMALL }>
			{ __( 'Recommended', 'amp' ) }
		</AMPNotice>
	);
}

/**
 * Small notice indicating a mode is not recommended.
 */
function NotRecommendedNotice() {
	return (
		<AMPNotice size={ NOTICE_SIZE_SMALL } type={ NOTICE_TYPE_WARNING }>
			{ __( 'Not recommended', 'amp' ) }
		</AMPNotice>
	);
}

/**
 * Provides the notice to show in the reader theme support mode selection.
 *
 * @param {boolean} selected Whether reader mode is selected.
 */
function getReaderNotice( selected ) {
	switch ( true ) {
		// Theme has built-in support or has declared theme support with the paired flag set to false.
		case selected && ( 'object' === typeof THEME_SUPPORT_ARGS && false === THEME_SUPPORT_ARGS.paired ):
			return {
				readerNoticeSmall: selected ? <NotRecommendedNotice /> : null,
				readerNoticeLarge: (
					<AMPNotice size={ NOTICE_SIZE_SMALL } type={ NOTICE_TYPE_WARNING }>
						{ __( 'Your active theme is known to work well in standard mode.', 'amp' ) }
					</AMPNotice>
				),
			};

		// Theme has built-in support or has declared theme support with the paired flag set to true.
		case selected && ( IS_CORE_THEME || ( 'object' === typeof THEME_SUPPORT_ARGS && false !== THEME_SUPPORT_ARGS.paired ) ):
			return {
				readerNoticeSmall: selected ? <NotRecommendedNotice /> : null,
				readerNoticeLarge: (
					<AMPNotice size={ NOTICE_SIZE_SMALL } type={ NOTICE_TYPE_WARNING }>
						{ __( 'Your active theme is known to work well in standard and transitional mode.', 'amp' ) }
					</AMPNotice>
				) };

		// Support for reader mode was detected.
		case THEME_SUPPORTS_READER_MODE:
			return {
				readerNoticeSmall: <RecommendedNotice />,
				readerNoticeLarge: (
					<AMPNotice size={ NOTICE_SIZE_SMALL }>
						{ __( 'Your theme indicates it has special support for the legacy templates in Reader mode.', 'amp' ) }
					</AMPNotice>
				) };

		default:
			return { readerNoticeSmall: null, readerNoticeLarge: null };
	}
}

/**
 * Template modes section of the settings page.
 */
export function TemplateModes() {
	const { editedOptions } = useContext( Options );
	const { selectedTheme } = useContext( ReaderThemes );

	const { theme_support: themeSupport } = editedOptions;

	const { readerNoticeSmall, readerNoticeLarge } = useMemo(
		() => getReaderNotice( READER === themeSupport ),
		[ themeSupport ],
	);

	return (
		<section className="template-modes">
			<h2>
				{ __( 'Template Mode', 'amp' ) }
			</h2>
			<p dangerouslySetInnerHTML={ {
				__html: __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="https://amp-wp.org/ecosystem/" target="_blank">ecosystem page</a>.', 'amp' ),
			} } />
			<TemplateModeOption
				details={ __( 'In Standard Mode your site uses a single theme and there is a single version of your content. In this mode, AMP is the framework of your site and there is reduced development and maintenance costs by having a single site to maintain.', 'amp' ) }
				initialOpen={ false }
				mode={ STANDARD }
				labelExtra={ ( IS_CORE_THEME || 'object' === typeof THEME_SUPPORT_ARGS ) ? <RecommendedNotice /> : null }
			>
				{
					// Plugin is not configured; active theme has built-in support or has declared theme support without the paired flag.
					( IS_CORE_THEME || 'object' === typeof THEME_SUPPORT_ARGS ) && (
						<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
							<p>
								{ __( 'Your active theme is known to work well in standard mode.', 'amp' ) }
							</p>
						</AMPNotice>
					)
				}
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'The active theme\'s templates are used to generate non-AMP and AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-first site. Depending on your themes/plugins, a varying level of development work may be required.', 'amp' ) }
				initialOpen={ false }
				mode={ TRANSITIONAL }
				labelExtra={ ( IS_CORE_THEME || 'object' === typeof THEME_SUPPORT_ARGS ) ? <RecommendedNotice /> : null }
			>
				{
					// Plugin is not configured; active theme has built-in support or has declared theme support with the paired flag.
					( IS_CORE_THEME || ( 'object' === typeof THEME_SUPPORT_ARGS && true === THEME_SUPPORT_ARGS.paired ) ) && (
						<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
							<p>
								{ __( 'Your active theme is known to work well in transitional mode.', 'amp' ) }
							</p>
						</AMPNotice>
					)
				}
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'Formerly called classic mode, this mode generates paired AMP content using simplified templates which may not match the look and feel of your site. Only posts/pages can be served as AMP in Reader mode. No redirection is performed for mobile visitors; AMP pages are served by AMP consumption platforms.', 'amp' ) }
				initialOpen={ false }
				mode={ READER }
				labelExtra={ readerNoticeSmall }
			>
				{ readerNoticeLarge }
			</TemplateModeOption>
			{ READER === themeSupport && (
				<AMPDrawer
					handleType={ HANDLE_TYPE_RIGHT }
					selected={ true }
					heading={ (
						<div className="reader-themes-drawer__heading">
							<h3>
								{ sprintf(
								// Translators: placeholder is a theme name.
									__( 'Reader theme: %s', 'amp' ),
									selectedTheme.name || '',
								) }
							</h3>
						</div>
					) }
					hiddenTitle={ __( 'Show reader themes', 'amp' ) }
					id="reader-themes-drawer"
					initialOpen={ false }
				>
					<ReaderThemeCarousel />
				</AMPDrawer>
			) }
		</section>
	);
}
