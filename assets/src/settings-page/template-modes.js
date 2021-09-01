/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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
import { AMPDrawer } from '../components/amp-drawer';
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
 *
 * @param {Object}  props                   Component props.
 * @param {boolean} props.focusReaderThemes Whether the reader themes drawer should be opened and focused.
 */
export function TemplateModes( { focusReaderThemes } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { selectedTheme, templateModeWasOverridden } = useContext( ReaderThemes );

	const { theme_support: themeSupport, sandboxing_level: sandboxingLevel } = editedOptions;

	const { readerNoticeSmall, readerNoticeLarge } = useMemo(
		() => getReaderNotice( READER === themeSupport ),
		[ themeSupport ],
	);

	return (
		<section className="template-modes" id="template-modes">
			<h2>
				{ __( 'Template Mode', 'amp' ) }
			</h2>
			{ templateModeWasOverridden && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					{ __( 'Because you selected a Reader theme that is the same as your site\'s active theme, your site has automatically been switched to Transitional template mode.', 'amp' ) }
				</AMPNotice>
			) }
			<TemplateModeOption
				details={ __( 'In Standard Mode your site uses a single theme and there is a single version of your content. You can opt out from AMP selectively for parts of your site. Every canonical URL will be either AMP or non-AMP.', 'amp' ) }
				detailsUrl="https://amp-wp.org/documentation/getting-started/standard/"
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
				{
					sandboxingLevel && (
						<fieldset>
							<h4 className="title">
								{ __( 'Sandboxing Level (Experimental)', 'amp' ) }
							</h4>
							<p>
								{ __( 'Try out a more flexible AMP by generating pages that use AMP components without requiring AMP validity! By selecting a sandboxing level, you are indicating the minimum degree of sanitization. For example, if you selected level 1 but have a page without any POST form and no custom scripts, it will still be served as valid AMP, the same as if you had selected level 3.', 'amp' ) }
							</p>
							<ol>
								<li>
									<input
										type="radio"
										id="sandboxing-level-1"
										checked={ 1 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 1 } );
										} }
									/>
									<label htmlFor="sandboxing-level-1">
										<strong>
											{ __( 'Loose:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Do not remove any AMP-invalid markup by default, including custom scripts. CSS tree-shaking is disabled.', 'amp' ) }
									</label>
								</li>
								<li>
									<input
										type="radio"
										id="sandboxing-level-2"
										checked={ 2 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 2 } );
										} }
									/>
									<label htmlFor="sandboxing-level-2">
										<strong>
											{ __( 'Moderate:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Remove non-AMP markup, but allow POST forms. CSS tree shaking is enabled.', 'amp' ) }
									</label>
								</li>
								<li>
									<input
										type="radio"
										id="sandboxing-level-3"
										checked={ 3 === sandboxingLevel }
										onChange={ () => {
											updateOptions( { sandboxing_level: 3 } );
										} }
									/>
									<label htmlFor="sandboxing-level-3">
										<strong>
											{ __( 'Strict:', 'amp' ) }
										</strong>
										{ ' ' + __( 'Require valid AMP.', 'amp' ) }
									</label>
								</li>

							</ol>
						</fieldset>
					)
				}
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'In Transitional mode the active theme\'s templates are used to generate both the AMP and non-AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-compatible site. Depending on your themes/plugins, a varying level of development work may be required.', 'amp' ) }
				detailsUrl="https://amp-wp.org/documentation/getting-started/transitional/"
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
				details={ __( 'In Reader mode, there are two versions of your site, and two different themes are used for the AMP and non-AMP versions. You have the option of using an existing AMP-compatible theme, or you can use the AMP Legacy theme (formerly known as Classic theme).', 'amp' ) }
				detailsUrl="https://amp-wp.org/documentation/getting-started/reader/"
				initialOpen={ false }
				mode={ READER }
				labelExtra={ readerNoticeSmall }
			>
				{ readerNoticeLarge }
			</TemplateModeOption>
			{ READER === themeSupport && (
				<AMPDrawer
					selected={ true }
					heading={ (
						<div className="reader-themes__heading">
							<h3>
								{ sprintf(
									/* translators: placeholder is a theme name. */
									__( 'Reader theme: %s', 'amp' ),
									selectedTheme.name || '',
								) }
							</h3>
						</div>
					) }
					hiddenTitle={ __( 'Show reader themes', 'amp' ) }
					id="reader-themes"
					initialOpen={ focusReaderThemes }
				>
					<ReaderThemeCarousel />
				</AMPDrawer>
			) }
		</section>
	);
}
TemplateModes.propTypes = {
	focusReaderThemes: PropTypes.bool.isRequired,
};
