/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { INITIAL_STATE, createStore } from './store';
import { withAMPToolbarButton } from './components/with-amp-toolbar-button';

createStore( INITIAL_STATE );

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { default: render, PLUGIN_NAME, PLUGIN_ICON } = plugins( modulePath );

	registerPlugin( PLUGIN_NAME, {
		icon: PLUGIN_ICON,
		render,
	} );
} );

addFilter( 'editor.BlockEdit', 'ampBlockValidation/filterEdit', withAMPToolbarButton, -99 );
