/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { registerCoreBlocks } from '@wordpress/block-library';

/**
 * Internal dependencies
 */
import App from './app';

/**
 * Initializes the block editor in the story edit screen.
 *
 * @param {Node} element           DOM element to render the screen in
 * @param {Object} config          Configuration for the editor
 */
export function initialize( element, config ) {
	registerCoreBlocks();
	render(
		<App
			config={ config }
		/>,
		element,
	);
}

if ( window && document.getElementById( 'edit-story' ) ) {
	const element = document.getElementById( 'edit-story' );
	const elementConfig = element.getAttribute( 'data-config' ) ? JSON.parse( element.getAttribute( 'data-config' ) ) : {};
	const settings = window.ampStoriesEditorSettings || {};
	const fonts = window.ampStoriesFonts || {};
	const exportSettings = window.ampStoriesExport || {};
	const config = {
		...elementConfig,
		settings,
		fonts,
		exportSettings,
	};
	initialize( element, config );
}
