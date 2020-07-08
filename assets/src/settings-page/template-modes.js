/**
 * External dependencies
 */
import {
	BUILT_IN_SUPPORT,
	THEME_PROVIDED_SUPPORT_MODE,
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
import '../css/template-mode-selection.css';
import { AMPNotice, NOTICE_SIZE_LARGE, NOTICE_TYPE_INFO, NOTICE_TYPE_WARNING } from '../components/amp-notice';
import { Options } from '../components/options-context-provider';

function getStandardNotice( themeSupport ) {
	switch ( true ) {
		case 'reader' === themeSupport && 'standard' === THEME_PROVIDED_SUPPORT_MODE:
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'Your active theme is known to work well in standard mode.', 'amp' ) }
					</p>
				</AMPNotice>
			);

		case 'reader' === themeSupport && ( BUILT_IN_SUPPORT || 'transitional' === THEME_PROVIDED_SUPPORT_MODE ):
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_WARNING }>
					<p>
						{ __( 'Your active theme is known to work well in transitional mode.', 'amp' ) }
					</p>
				</AMPNotice>
			);

		case THEME_SUPPORTS_READER_MODE:
			return (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'Your theme indicates it works best in reader mode.', 'amp' ) }
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

	const { plugin_configured: pluginConfigured, theme_support: themeSupport } = editedOptions;

	return (
		<section className="template-mode-selection">
			<h2>
				{ __( 'Template mode', 'amp' ) }
			</h2>
			<p dangerouslySetInnerHTML={ {
				__html: __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="https://amp-wp.org/ecosystem/">ecosystem page</a>.', 'amp' ),
			} } />
			<TemplateModeOption
				details={ __( 'In Standard Mode your site uses a single theme and there is a single version of your content. In this mode, AMP is the framework of your site and there is reduced development and maintenance costs by having a single site to maintain.', 'amp' ) }
				mode="standard"
			>
				{ ! pluginConfigured && 'standard' === THEME_PROVIDED_SUPPORT_MODE && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						<p>
							{ __( 'Your active theme is known to work well in standard mode.', 'amp' ) }
						</p>
					</AMPNotice>
				) }
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'The active theme\'s templates are used to generate non-AMP and AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-first site. Depending on your themes/plugins, a varying level of development work may be required.', 'amp' ) }
				mode="transitional"
			>
				{ ! pluginConfigured && ( BUILT_IN_SUPPORT || 'transitional' === THEME_PROVIDED_SUPPORT_MODE ) && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						<p>
							{ __( 'Your active theme is known to work well in transitional mode.', 'amp' ) }
						</p>
					</AMPNotice>
				) }
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'Formerly called classic mode, this mode generates paired AMP content using simplified templates which may not match the look and feel of your site. Only posts/pages can be served as AMP in Reader mode. No reidrection is performed for mobile visitors; AMP pages are served by AMP consumption platforms.', 'amp' ) }
				mode="reader"
			>
				{ getStandardNotice( themeSupport ) }
			</TemplateModeOption>
		</section>
	);
}
