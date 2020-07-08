/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	CURRENT_THEME,
	OPTIONS_REST_ENDPOINT,
	READER_THEMES_REST_ENDPOINT,
	THEME_SUPPORT_ARGS,
	UPDATES_NONCE,
} from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import { OptionsContextProvider } from '../components/options-context-provider';
import { AMPNotice, NOTICE_TYPE_WARNING, NOTICE_SIZE_LARGE } from '../components/amp-notice';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { TemplateModes } from './template-modes';
import { SupportedTemplates } from './supported-templates';
import { MobileRedirection } from './mobile-redirection';
import { ReaderThemes } from './reader-themes';
import { SettingsFooter } from './settings-footer';
import { PluginSuppression } from './plugin-suppression';

const { ajaxurl: wpAjaxUrl } = global;

/**
 * Context providers for the settings page.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Context consumers.
 */
function Providers( { children } ) {
	return (
		<OptionsContextProvider optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }>
			<ReaderThemesContextProvider
				currentTheme={ CURRENT_THEME }
				readerThemesEndpoint={ READER_THEMES_REST_ENDPOINT }
				updatesNonce={ UPDATES_NONCE }
				wpAjaxUrl={ wpAjaxUrl }
			>
				{ children }
			</ReaderThemesContextProvider>
		</OptionsContextProvider>
	);
}
Providers.propTypes = {
	children: PropTypes.any,
};

/**
 * Settings page application root.
 */
function Root() {
	const themeSupportArgs = Array.isArray( THEME_SUPPORT_ARGS ) ? {} : THEME_SUPPORT_ARGS;

	return (
		<>
			{
				themeSupportArgs && 'available_callback' in themeSupportArgs && (
					<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_LARGE }>
						<p>
							{ __( 'Your theme is using the deprecated available_callback argument for AMP theme support.', 'amp' ) }
						</p>
					</AMPNotice>
				)
			}
			<TemplateModes />
			<ReaderThemes />
			<SupportedTemplates themeSupportArgs={ themeSupportArgs } />
			<MobileRedirection />
			<PluginSuppression />
			<SettingsFooter />
		</>
	);
}

domReady( () => {
	const root = document.getElementById( 'amp-settings-root' );

	if ( root ) {
		render( (
			<Providers>
				<Root optionsRestEndpoint={ OPTIONS_REST_ENDPOINT } />
			</Providers>
		), root );
	}
} );
