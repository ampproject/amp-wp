/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	APP_ROOT_ID,
	APP_ROOT_SIBLING_ID,
	OPTIONS_REST_PATH,
	SCANNABLE_URLS_REST_PATH,
	VALIDATE_NONCE,
} from 'amp-site-scan-notice'; // From WP inline script.

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import { ErrorContextProvider } from '../../components/error-context-provider';
import { OptionsContextProvider } from '../../components/options-context-provider';
import { PluginsContextProvider } from '../../components/plugins-context-provider';
import { SiteScanContextProvider } from '../../components/site-scan-context-provider';
import { ThemesContextProvider } from '../../components/themes-context-provider';
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
function Providers({ children }) {
	global.removeEventListener('error', errorHandler);

	return (
		<ErrorContextProvider>
			<ErrorBoundary
				title={__(
					'The AMP Site Scanner has experienced an error.',
					'amp'
				)}
			>
				<OptionsContextProvider
					hasErrorBoundary={true}
					optionsRestPath={OPTIONS_REST_PATH}
					populateDefaultValues={false}
				>
					<PluginsContextProvider>
						<ThemesContextProvider>
							<SiteScanContextProvider
								scannableUrlsRestPath={SCANNABLE_URLS_REST_PATH}
								validateNonce={VALIDATE_NONCE}
							>
								{children}
							</SiteScanContextProvider>
						</ThemesContextProvider>
					</PluginsContextProvider>
				</OptionsContextProvider>
			</ErrorBoundary>
		</ErrorContextProvider>
	);
}
Providers.propTypes = {
	children: PropTypes.any,
};

domReady(() => {
	let root = document.getElementById(APP_ROOT_ID);

	if (!root) {
		const rootSibling = document.getElementById(APP_ROOT_SIBLING_ID);

		if (!rootSibling) {
			return;
		}

		root = document.createElement('div');
		root.setAttribute('id', APP_ROOT_ID);
		rootSibling.after(root);
	}

	errorHandler = (event) => {
		// Handle only own errors.
		if (
			event.filename &&
			/amp-site-scan-notice(\.min)?\.js/.test(event.filename)
		) {
			createRoot(root).render(<ErrorScreen error={event.error} />);
		}
	};

	global.addEventListener('error', errorHandler);

	createRoot(root).render(
		<Providers>
			<SiteScanNotice />
		</Providers>
	);
});
