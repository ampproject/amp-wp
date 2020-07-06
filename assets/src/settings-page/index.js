/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { OPTIONS_REST_ENDPOINT } from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { OptionsContextProvider } from '../components/options-context-provider';
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './welcome.css';
import { TemplateModes } from './template-modes';
import { SupportedTemplates } from './supported-templates';
import { MobileRedirection } from './mobile-redirection';

/**
 * External dependencies
 */

function Root( { optionsRestEndpoint } ) {
	return (
		<OptionsContextProvider optionsRestEndpoint={ optionsRestEndpoint }>
			<TemplateModes />
			<SupportedTemplates />
			<MobileRedirection />
		</OptionsContextProvider>
	);
}
Root.propTypes = {
	optionsRestEndpoint: PropTypes.string.isRequired,
};

domReady( () => {
	const root = document.getElementById( 'amp-settings-root' );

	if ( root ) {
		render( <Root optionsRestEndpoint={ OPTIONS_REST_ENDPOINT } />, root );
	}
} );
