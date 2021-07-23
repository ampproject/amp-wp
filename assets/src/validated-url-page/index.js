/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	APP_ROOT_ID,
	CSS_BUDGET_BYTES,
	CSS_BUDGET_WARNING_PERCENTAGE,
	POST_ID,
	VALIDATED_URLS_REST_PATH,
} from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ErrorBoundary } from '../components/error-boundary';
import { ErrorContextProvider } from '../components/error-context-provider';
import { ErrorScreen } from '../components/error-screen';
import { ValidatedUrlProvider } from '../components/validated-url-provider';
import Stylesheets from './stylesheets';

let errorHandler;

/**
 * Context providers for the settings page.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Context consumers.
 */
function Providers( { children } ) {
	global.removeEventListener( 'error', errorHandler );

	return (
		<ErrorContextProvider>
			<ErrorBoundary>
				<ValidatedUrlProvider
					cssBudgetBytes={ Number( CSS_BUDGET_BYTES ) }
					cssBudgetWarningPercentage={ Number( CSS_BUDGET_WARNING_PERCENTAGE ) }
					hasErrorBoundary={ true }
					postId={ Number( POST_ID ) }
					validatedUrlsRestPath={ VALIDATED_URLS_REST_PATH }
				>
					{ children }
				</ValidatedUrlProvider>
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
		if ( event.filename && /amp-validated-url-page(\.min)?\.js/.test( event.filename ) ) {
			render( <ErrorScreen error={ event.error } />, root );
		}
	};

	global.addEventListener( 'error', errorHandler );

	render(
		<Providers>
			<Stylesheets appRoot={ root } />
		</Providers>,
		root,
	);
} );
