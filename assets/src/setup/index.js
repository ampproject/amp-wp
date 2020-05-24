/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import '@wordpress/components/build-style/style.css';

/**
 * External dependencies
 */
import { APP_ROOT_ID, EXIT_LINK, OPTIONS_REST_ENDPOINT } from 'amp-setup'; // From WP inline script.

/**
 * Internal dependencies
 */
import './style.css';
import { PAGES } from './pages';
import { OptionsContextProvider } from './components/options-context-provider';
import { SetupWizard } from './setup-wizard';
import { CacheContextProvider } from './components/cache-context-provider';

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( root ) {
		render(
			<OptionsContextProvider optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }>
				<CacheContextProvider>
					<SetupWizard exitLink={ EXIT_LINK } pages={ PAGES } />
				</CacheContextProvider>
			</OptionsContextProvider>,
			root,
		);
	}
} );
