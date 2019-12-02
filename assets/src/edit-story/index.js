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
	render(
		<App
			config={ config }
		/>,
		document.getElementById( id ),
	);
};

domReady( () => {
	const { id, config } = window.ampStoriesEditSettings;
	initialize( id, config );
} );
