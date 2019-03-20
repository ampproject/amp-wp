/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

const context = require.context( './blocks', true, /((?<!story.*)\/index\.js)$/ );

context.keys().forEach( ( modulePath ) => {
	const { name, settings } = context( modulePath );
	registerBlockType( name, settings );
} );
