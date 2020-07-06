/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

/**
 * External dependencies
 */
import { OPTIONS_REST_ENDPOINT } from 'amp-settings';

/**
 * Internal dependencies
 */
import { OptionsContextProvider } from '../components/options-context-provider';
import { Welcome } from './welcome';
import '../css/variables.css';
import '../css/elements.css';

/**
 * External dependencies
 */

function Root() {
	return (
		<OptionsContextProvider optionsRestEndpoint={ OPTIONS_REST_ENDPOINT }>
			<Welcome />
			{ '' }
		</OptionsContextProvider>
	);
}

domReady( () => {
	const root = document.getElementById( 'amp-settings-root' );

	if ( root ) {
		render( <Root />, root );
	}
} );
