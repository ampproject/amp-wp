/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { OPTIONS_REST_ENDPOINT, THEME_SUPPORT_ARGS, THEME_SUPPORT_NOTICES } from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render, createPortal, useContext, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { OptionsContextProvider, Options } from '../components/options-context-provider';
import { AMPNotice, NOTICE_TYPE_WARNING, NOTICE_SIZE_LARGE } from '../components/amp-notice';
import { Loading } from '../components/loading';
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

/**
 * Context providers for the settings page.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Context consumers.
 */
function Providers( { children } ) {
	return (
		<OptionsContextProvider optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }>
			{ children }
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
 */
function Root() {
	const { didSaveOptions, fetchingOptions, saveOptions, savingOptions } = useContext( Options );

	const themeSupportArgs = Array.isArray( THEME_SUPPORT_ARGS ) ? {} : THEME_SUPPORT_ARGS;

	const TemplateModesPortal = () => createPortal(
		<TemplateModes themeSupportNotices={ THEME_SUPPORT_NOTICES } />,
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
		<Button isPrimary onClick={ saveOptions } disabled={ savingOptions || didSaveOptions }>
			{ __( 'Save changes', 'amp' ) }
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
				<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
			</svg>
		</Button>,
		document.getElementById( 'amp-settings-footer' ),
	);

	/**
	 * Submits the PHP-generated form on the page after options have saved via REST.
	 */
	useEffect( () => {
		if ( true === didSaveOptions ) {
			document.querySelector( 'form#amp-settings' ).submit();
		}
	}, [ didSaveOptions ] );

	return (
		<>
			{ fetchingOptions && <Loading /> }
			{
				'available_callback' in themeSupportArgs && (
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
