/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * External dependencies
 */
import {
	APP_ROOT_ID,
	CLOSE_LINK,
	CURRENT_THEME,
	FINISH_LINK,
	OPTIONS_REST_ENDPOINT,
	READER_THEMES_REST_ENDPOINT,
	UPDATES_NONCE,
	USER_FIELD_DEVELOPER_TOOLS_ENABLED,
	USER_REST_ENDPOINT,
} from 'amp-settings'; // From WP inline script.
import PropTypes from 'prop-types';

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
import { PAGES } from './pages';
import { SetupWizard } from './setup-wizard';
import { NavigationContextProvider } from './components/navigation-context-provider';
import { UserContextProvider } from './components/user-context-provider';
import { SiteScanContextProvider } from './components/site-scan-context-provider';
import { TemplateModeOverrideContextProvider } from './components/template-mode-override-context-provider';

const { ajaxurl: wpAjaxUrl } = global;

/**
 * Context providers for the application.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 */
export function Providers( { children } ) {
	return (
		<OptionsContextProvider
			optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }
			populateDefaultValues={ false }
		>
			<UserContextProvider
				userOptionDeveloperTools={ USER_FIELD_DEVELOPER_TOOLS_ENABLED }
				userRestEndpoint={ USER_REST_ENDPOINT }
			>
				<NavigationContextProvider pages={ PAGES }>
					<ReaderThemesContextProvider
						currentTheme={ CURRENT_THEME }
						wpAjaxUrl={ wpAjaxUrl }
						readerThemesEndpoint={ READER_THEMES_REST_ENDPOINT }
						updatesNonce={ UPDATES_NONCE }
					>
						<TemplateModeOverrideContextProvider>
							<SiteScanContextProvider>
								{ children }
							</SiteScanContextProvider>
						</TemplateModeOverrideContextProvider>
					</ReaderThemesContextProvider>
				</NavigationContextProvider>
			</UserContextProvider>
		</OptionsContextProvider>
	);
}

Providers.propTypes = {
	children: PropTypes.any,
};

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( root ) {
		render(
			<ErrorBoundary exitLink={ FINISH_LINK }>
				<Providers>
					<SetupWizard closeLink={ CLOSE_LINK } finishLink={ FINISH_LINK } />
				</Providers>
			</ErrorBoundary>,
			root,
		);
	}
} );
