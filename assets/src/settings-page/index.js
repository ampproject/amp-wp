/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	CURRENT_THEME,
	OPTIONS_REST_ENDPOINT,
	READER_THEMES_REST_ENDPOINT,
	UPDATES_NONCE,
} from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import { OptionsContextProvider } from '../components/options-context-provider';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { TemplateModes } from './template-modes';
import { SupportedTemplates } from './supported-templates';
import { SupportedTemplates as OldSupportedTemplates } from './old-supported-templates';
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
	return (
		<>
			<TemplateModes />
			<ReaderThemes />
			<SupportedTemplates />
			<OldSupportedTemplates />
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
