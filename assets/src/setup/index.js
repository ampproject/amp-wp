/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { APP_ROOT_ID } from 'amp-setup';

function Setup() {
	return (
		<div>
			{ __( 'Setup Wizard', 'amp' ) }
		</div>
	);
}

domReady( () => {
	const root = document.getElementById( APP_ROOT_ID );

	if ( root ) {
		render( <Setup />, root );
	}
} );
