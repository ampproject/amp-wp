/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { registerCoreBlocks } from '@wordpress/block-library';

/**
 * Internal dependencies
 */
import Layout from './components/layout';

/**
 * Initializes the block editor in the story edit screen.
 *
 * @param {string} id  ID of the root element to render the screen in.
 */
export function initialize( id ) {
	registerCoreBlocks();
	render(
		<Layout />,
		document.getElementById( id ),
	);
}

if ( window && document.getElementById( 'edit-story' ) ) {
	initialize( 'edit-story' );
}
