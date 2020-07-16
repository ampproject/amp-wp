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
import { render, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import { Panel, PanelBody } from '@wordpress/components';
import { OptionsContextProvider, Options } from '../components/options-context-provider';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { SiteSettingsProvider } from '../components/site-settings-provider';
import { Loading } from '../components/loading';
import { UnsavedChangesWarning } from '../components/unsaved-changes-warning';
import { AMPNotice, NOTICE_TYPE_WARNING } from '../components/amp-notice';
import { ErrorContextProvider, ErrorContext } from '../components/error-context-provider';
import { Welcome } from './welcome';
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
		<SiteSettingsProvider>
			<OptionsContextProvider optionsRestEndpoint={ OPTIONS_REST_ENDPOINT } populateDefaultValues={ true }>
				<ReaderThemesContextProvider
					currentTheme={ CURRENT_THEME }
					readerThemesEndpoint={ READER_THEMES_REST_ENDPOINT }
					updatesNonce={ UPDATES_NONCE }
					wpAjaxUrl={ wpAjaxUrl }
				>
					{ children }
				</ReaderThemesContextProvider>
			</OptionsContextProvider>
		</SiteSettingsProvider>
	);
}
Providers.propTypes = {
	children: PropTypes.any,
};

/**
 * Renders an error notice.
 *
 * @param {Object} props Component props.
 * @param {string} props.errorMessage Error message text.
 */
function ErrorNotice( { errorMessage } ) {
	return (
		<div className="amp-error-notice">
			<AMPNotice type={ NOTICE_TYPE_WARNING }>
				<p>
					<strong>
						{ __( 'Error:', 'amp' ) }
					</strong>
					{ ' ' }
					{ errorMessage }
				</p>
			</AMPNotice>
		</div>
	);
}
ErrorNotice.propTypes = {
	errorMessage: PropTypes.string,
};

/**
 * Settings page application root.
 */
function Root() {
	const { fetchingOptions } = useContext( Options );
	const { error } = useContext( ErrorContext );

	if ( false !== fetchingOptions ) {
		return <Loading />;
	}

	return (
		<>
			<Welcome />
			<TemplateModes />
			<ReaderThemes />
			<Panel className="advanced-settings-container">
				<PanelBody title={ __( 'Advanced', 'amp' ) } initialOpen={ false }>
					<div className="advanced-settings">
						<SupportedTemplates />
						<MobileRedirection />
						<PluginSuppression />
					</div>
				</PanelBody>
			</Panel>
			<SettingsFooter />
			<UnsavedChangesWarning excludeUserContext={ true } />
			{ error && <ErrorNotice errorMessage={ error.message || __( 'An error occurred. You might be offline or logged out.', 'amp' ) } /> }
		</>
	);
}

domReady( () => {
	const root = document.getElementById( 'amp-settings-root' );

	if ( root ) {
		render( (
			<ErrorContextProvider>
				<Providers>
					<Root optionsRestEndpoint={ OPTIONS_REST_ENDPOINT } />
				</Providers>
			</ErrorContextProvider>
		), root );
	}
} );
