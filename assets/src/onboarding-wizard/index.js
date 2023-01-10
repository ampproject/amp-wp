/**
 * External dependencies
 */
import {
	AMP_SCAN_IF_STALE,
	APP_ROOT_ID,
	CLOSE_LINK,
	CURRENT_THEME,
	SETTINGS_LINK,
	OPTIONS_REST_PATH,
	READER_THEMES_REST_PATH,
	SCANNABLE_URLS_REST_PATH,
	UPDATES_NONCE,
	USER_FIELD_DEVELOPER_TOOLS_ENABLED,
	USERS_RESOURCE_REST_PATH,
	VALIDATE_NONCE,
} from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createRoot, StrictMode } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/core-components.css';
import '../css/elements.css';
import './style.css';
import { OptionsContextProvider } from '../components/options-context-provider';
import { ReaderThemesContextProvider } from '../components/reader-themes-context-provider';
import { ErrorBoundary } from '../components/error-boundary';
import { ErrorContextProvider } from '../components/error-context-provider';
import { ErrorScreen } from '../components/error-screen';
import { SiteScanContextProvider } from '../components/site-scan-context-provider';
import { UserContextProvider } from '../components/user-context-provider';
import { PluginsContextProvider } from '../components/plugins-context-provider';
import { ThemesContextProvider } from '../components/themes-context-provider';
import { PAGES } from './pages';
import { SetupWizard } from './setup-wizard';
import { NavigationContextProvider } from './components/navigation-context-provider';
import { TemplateModeOverrideContextProvider } from './components/template-mode-override-context-provider';

const { ajaxurl: wpAjaxUrl } = global;

let errorHandler;

/**
 * Context providers for the application.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Component children.
 */
export function Providers({ children }) {
	global.removeEventListener('error', errorHandler);

	return (
		<ErrorContextProvider>
			<ErrorBoundary
				exitLinkLabel={__('Return to AMP settings.', 'amp')}
				exitLinkUrl={SETTINGS_LINK}
				title={__('The setup wizard has experienced an error.', 'amp')}
			>
				<OptionsContextProvider
					delaySave={true}
					hasErrorBoundary={true}
					optionsRestPath={OPTIONS_REST_PATH}
					populateDefaultValues={false}
				>
					<UserContextProvider
						userOptionDeveloperTools={
							USER_FIELD_DEVELOPER_TOOLS_ENABLED
						}
						usersResourceRestPath={USERS_RESOURCE_REST_PATH}
					>
						<PluginsContextProvider>
							<ThemesContextProvider>
								<SiteScanContextProvider
									fetchCachedValidationErrors={false}
									resetOnOptionsChange={true}
									scannableUrlsRestPath={
										SCANNABLE_URLS_REST_PATH
									}
									scanOnce={true}
									validateNonce={VALIDATE_NONCE}
								>
									<NavigationContextProvider pages={PAGES}>
										<ReaderThemesContextProvider
											currentTheme={CURRENT_THEME}
											hasErrorBoundary={true}
											wpAjaxUrl={wpAjaxUrl}
											readerThemesRestPath={
												READER_THEMES_REST_PATH
											}
											updatesNonce={UPDATES_NONCE}
										>
											<TemplateModeOverrideContextProvider>
												{children}
											</TemplateModeOverrideContextProvider>
										</ReaderThemesContextProvider>
									</NavigationContextProvider>
								</SiteScanContextProvider>
							</ThemesContextProvider>
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

domReady(() => {
	const root = document.getElementById(APP_ROOT_ID);

	if (!root) {
		return;
	}

	errorHandler = (event) => {
		// Handle only own errors.
		if (
			event.filename &&
			/amp-onboarding-wizard(\.min)?\.js/.test(event.filename)
		) {
			createRoot(root).render(
				<StrictMode>
					<ErrorScreen error={event.error} />
				</StrictMode>
			);
		}
	};

	global.addEventListener('error', errorHandler);

	createRoot(root).render(
		<StrictMode>
			<Providers>
				<SetupWizard
					closeLink={addQueryArgs(CLOSE_LINK, {
						[AMP_SCAN_IF_STALE]: 1,
					})}
					finishLink={addQueryArgs(SETTINGS_LINK, {
						[AMP_SCAN_IF_STALE]: 1,
					})}
					appRoot={root}
				/>
			</Providers>
		</StrictMode>
	);
});
