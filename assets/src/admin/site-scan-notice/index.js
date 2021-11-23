/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	APP_ROOT_ID,
	OPTIONS_REST_PATH,
	SCANNABLE_URLS_REST_PATH,
	USER_FIELD_DEVELOPER_TOOLS_ENABLED,
	USERS_RESOURCE_REST_PATH,
	VALIDATE_NONCE,
} from 'amp-site-scan-notice'; // From WP inline script.

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ErrorContextProvider } from '../../components/error-context-provider';
import { OptionsContextProvider } from '../../components/options-context-provider';
import { PluginsContextProvider } from '../../components/plugins-context-provider';
import { SiteScanContextProvider } from '../../components/site-scan-context-provider';
import { UserContextProvider } from '../../components/user-context-provider';
import { ErrorScreen } from '../../components/error-screen';
import { ErrorBoundary } from '../../components/error-boundary';
import { SiteScanNotice } from './notice';

let errorHandler;

/**
 * Context providers for the application.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Component children.
 */
function Providers( { children } ) {
	global.removeEventListener( 'error', errorHandler );

	return (
		<ErrorContextProvider>
			<ErrorBoundary
				title={ __( 'The Site Scanner has experienced an error.', 'amp' ) }
			>
				<OptionsContextProvider
					hasErrorBoundary={ true }
					optionsRestPath={ OPTIONS_REST_PATH }
					populateDefaultValues={ false }
				>
					<UserContextProvider
						onlyFetchIfPluginIsConfigured={ true }
						userOptionDeveloperTools={ USER_FIELD_DEVELOPER_TOOLS_ENABLED }
						usersResourceRestPath={ USERS_RESOURCE_REST_PATH }
					>
						<PluginsContextProvider hasErrorBoundary={ true }>
							<SiteScanContextProvider
								scannableUrlsRestPath={ SCANNABLE_URLS_REST_PATH }
								validateNonce={ VALIDATE_NONCE }
							>
								{ children }
							</SiteScanContextProvider>
						</PluginsContextProvider>
					</UserContextProvider>
				</OptionsContextProvider>
			</ErrorBoundary>
		</ErrorContextProvider>
	);
}
Providers.propTypes = {
	children: PropTypes.any,
};

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( ! root ) {
		return;
	}

	errorHandler = ( event ) => {
		// Handle only own errors.
		if ( event.filename && /amp-site-scan-notice(\.min)?\.js/.test( event.filename ) ) {
			render( <ErrorScreen error={ event.error } />, root );
		}
	};

	global.addEventListener( 'error', errorHandler );

	render(
		<Providers>
			<SiteScanNotice />
		</Providers>,
		root,
	);
} );
