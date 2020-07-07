/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	CURRENT_THEME,
	OPTIONS_REST_ENDPOINT,
	READER_THEMES_REST_ENDPOINT,
	THEME_SUPPORT_ARGS,
	THEME_SUPPORT_NOTICES,
	UPDATES_NONCE,
} from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render, createPortal } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { OptionsContextProvider } from '../components/options-context-provider';
import { AMPNotice, NOTICE_TYPE_WARNING, NOTICE_SIZE_LARGE } from '../components/amp-notice';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { TemplateModes } from './template-modes';
import { SupportedTemplates } from './supported-templates';
import { MobileRedirection } from './mobile-redirection';

/**
 * Styles.
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import { ReaderThemes } from './reader-themes';
import { SettingsFooter } from './settings-footer';

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
 * Settings page application root. Because there is PHP-generated markup, including a form, in the middle of components that share
 * React state, several root divs have been created on the backend, and in this component we load various pieces of the application
 * into those root components, all sharing state.
 *
 * @todo Once the template support form is fully moved from PHP into React, all of this can be rendered into a single root div.
 *
 * Note: This component cannot use any state or context. They would cause the portals to be re-created every time state change.
 */
function Root() {
	const themeSupportArgs = Array.isArray( THEME_SUPPORT_ARGS ) ? {} : THEME_SUPPORT_ARGS;

	const TemplateModesPortal = () => createPortal(
		(
			<>
				<TemplateModes themeSupportNotices={ THEME_SUPPORT_NOTICES } />
				<ReaderThemes />
			</>
		),
		document.getElementById( 'amp-template-modes' ),
	);

	const SupportedTemplatesPortal = () => createPortal(
		<SupportedTemplates themeSupportArgs={ themeSupportArgs } />,
		document.getElementById( 'amp-supported-templates' ),
	);

	const MobileRedirectionPortal = () => createPortal(
		<MobileRedirection />,
		document.getElementById( 'amp-mobile-redirect' ),
	);

	const SettingsFooterPortal = () => createPortal(
		<SettingsFooter />,
		document.getElementById( 'amp-settings-footer' ),
	);

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
			<TemplateModesPortal />
			<SupportedTemplatesPortal />
			<MobileRedirectionPortal />
			<SettingsFooterPortal />
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
