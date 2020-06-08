/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import '@wordpress/components/build-style/style.css';

/**
 * External dependencies
 */
import { AMP_OPTIONS_KEY, APP_ROOT_ID, EXIT_LINK, OPTIONS_REST_ENDPOINT, READER_THEMES_ENDPOINT, UPDATES_NONCE } from 'amp-setup'; // From WP inline script.
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import { PAGES } from './pages';
import { OptionsContextProvider } from './components/options-context-provider';
import { SetupWizard } from './setup-wizard';
import { NavigationContextProvider } from './components/navigation-context-provider';
import { ReaderThemesContextProvider } from './components/reader-themes-context-provider';

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
			optionsKey={ AMP_OPTIONS_KEY }
			optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }
		>
			<ReaderThemesContextProvider
				wpAjaxUrl={ wpAjaxUrl }
				readerThemesEndpoint={ READER_THEMES_ENDPOINT }
				updatesNonce={ UPDATES_NONCE }
			>
				<NavigationContextProvider pages={ PAGES }>
					{ children }
				</NavigationContextProvider>
			</ReaderThemesContextProvider>
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
			<Providers>
				<SetupWizard exitLink={ EXIT_LINK } />
			</Providers>,
			root,
		);
	}
} );
