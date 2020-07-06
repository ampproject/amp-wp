/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { OPTIONS_REST_ENDPOINT } from 'amp-settings';

/**
 * Internal dependencies
 */
import { OptionsContextProvider } from '../components/options-context-provider';
import '../css/variables.css';
import '../css/elements.css';
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
