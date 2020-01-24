/**
 * External dependencies
 */
import Modal from 'react-modal';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import App from './app';
import './style.css'; // This way the general editor styles are loaded before all the component styles.

/**
 * Initializes the block editor in the widgets screen.
 *
 * @param {string} id       ID of the root element to render the screen in.
 * @param {Object} config   Story editor settings.
 */
const initialize = ( id, config ) => {
	const appElement = document.getElementById( id );

	// see http://reactcommunity.org/react-modal/accessibility/
	Modal.setAppElement( appElement );

	render(
		<App
			config={ config }
		/>,
		appElement,
	);
};

domReady( () => {
	const { id, config } = window.ampStoriesEditSettings;
	initialize( id, config );
} );
