/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

const context = require.context( './blocks', true, /index\.js$/ );

context.keys().forEach( ( modulePath ) => {
	const { name, settings } = context( modulePath );

	if ( name.includes( 'story' ) ) {
		return;
	}

	registerBlockType( name, settings );
} );
