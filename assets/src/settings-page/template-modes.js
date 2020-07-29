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
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TemplateModeOption } from '../components/template-mode-option';
import { AMPNotice, NOTICE_SIZE_LARGE, NOTICE_TYPE_INFO } from '../components/amp-notice';
import { Options } from '../components/options-context-provider';
import { ReaderThemes } from './reader-themes';

/**
 * Provides the notice to show in the reader theme support mode selection.
 *
 * @param {string} themeSupport The current theme support mode.
 */
function getReaderNotice( themeSupport ) {
	switch ( true ) {
		// Theme has built-in support or has declared theme support with the paired flag set to false.
		case 'reader' === themeSupport && ( 'object' === typeof THEME_SUPPORT_ARGS && false === THEME_SUPPORT_ARGS.paired ):
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'Your active theme is known to work well in standard mode.', 'amp' ) }
					</p>
				</AMPNotice>
			);

		// Theme has built-in support or has declared theme support with the paired flag set to true.
		case 'reader' === themeSupport && ( IS_CORE_THEME || ( 'object' === typeof THEME_SUPPORT_ARGS && false !== THEME_SUPPORT_ARGS.paired ) ):
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'Your active theme is known to work well in standard and transitional mode.', 'amp' ) }
					</p>
				</AMPNotice>
			);

		// Support for reader mode was detected.
		case THEME_SUPPORTS_READER_MODE:
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'Your theme indicates it has special support for the legacy templates in Reader mode.', 'amp' ) }
					</p>
				</AMPNotice>
			);

		default:
			return null;
	}
}

/**
 * Template modes section of the settings page.
 */
export function TemplateModes() {
	const { editedOptions } = useContext( Options );
	const { theme_support: themeSupport } = editedOptions;

	return (
		<section className="template-modes">
			<h2>
				{ __( 'Template mode', 'amp' ) }
			</h2>
			<TemplateModeOption
				details={ __( 'In Standard mode your site uses a single theme and there is a single version of your content. You can opt out from AMP selectively for parts of your site. Every canonical URL will be either AMP or non-AMP.', 'amp' ) }
				mode="standard"
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
				details={ __( 'In transitional mode the active theme\'s templates are used to generate both the AMP and non-AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-compatible site. Depending on your themes/plugins, a varying level of development work may be required.', 'amp' ) }
				mode="transitional"
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
				details={ __( 'In Reader mode, there are two versions of your site, and two different themes are used are used for the AMP and non-AMP versions. You have the option of using an available fully AMP-compatible theme, or you can use the AMP Legacy theme (formerly known as Classic theme).', 'amp' ) }
				mode="reader"
			>
				{ getReaderNotice( themeSupport ) }
				{ 'reader' === themeSupport && <ReaderThemes /> }
			</TemplateModeOption>
		</section>
	);
}
