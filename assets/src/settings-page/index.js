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
import { render, useContext, useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import { OptionsContextProvider } from '../components/options-context-provider';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { SiteSettingsProvider } from '../components/site-settings-provider';
import { UnsavedChangesWarning } from '../components/unsaved-changes-warning';
import { ErrorBoundary, ErrorContext } from '../components/error-boundary';
import { AMPNotice, NOTICE_TYPE_WARNING } from '../components/amp-notice';
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

function ErrorNotice( { errorMessage } ) {
	return (
		<div className="amp-error-notice">
			<AMPNotice type={ NOTICE_TYPE_WARNING }>
				<p>
					<strong>
						{ __( 'Error: ', 'amp' ) }
					</strong>
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
	const error = useContext( ErrorContext );
	const [ delayedError, setDelayedError ] = useState( error && error.message ? error.message : null );
	const errorRef = useRef( error );

	useEffect( () => {
		errorRef.current = error;
	}, [ error ] );

	useEffect( () => {
		const interval = setInterval( () => {
			if ( delayedError && ! errorRef.current ) {
				setDelayedError( null );
				return;
			}

			if ( errorRef.current && delayedError !== errorRef.current.message ) {
				setDelayedError( errorRef.current.message );
			}
		}, 1500 );

		return () => {
			clearInterval( interval );
		};
	}, [ delayedError ] );

	return (
		<>
			<TemplateModes />
			<ReaderThemes />
			<SupportedTemplates />
			<MobileRedirection />
			<PluginSuppression />
			<SettingsFooter />
			<UnsavedChangesWarning excludeUserContext={ true } />
			{ delayedError && <ErrorNotice errorMessage={ delayedError } /> }
		</>
	);
}

domReady( () => {
	const root = document.getElementById( 'amp-settings-root' );

	if ( root ) {
		render( (
			<ErrorBoundary>
				<Providers>
					<Root optionsRestEndpoint={ OPTIONS_REST_ENDPOINT } />
				</Providers>
			</ErrorBoundary>
		), root );
	}
} );
