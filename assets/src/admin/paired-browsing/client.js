/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const { parent } = window;

if ( parent.pairedBrowsingApp ) {
	window.ampPairedBrowsingClient = true;
	const app = parent.pairedBrowsingApp;

	app.registerClientWindow( window );

	domReady( () => {
		if ( app.documentIsAmp( document ) ) {
			// Hide the paired browsing menu item.
			const pairedBrowsingMenuItem = document.getElementById( 'wp-admin-bar-amp-paired-browsing' );
			if ( pairedBrowsingMenuItem ) {
				pairedBrowsingMenuItem.remove();
			}

			// Hide menu item to view non-AMP version.
			const ampViewBrowsingItem = document.getElementById( 'wp-admin-bar-amp-view' );
			if ( ampViewBrowsingItem ) {
				ampViewBrowsingItem.remove();
			}
		} else {
			/**
			 * No need to show the AMP menu in the Non-AMP window.
			 */
			const ampMenuItem = document.getElementById( 'wp-admin-bar-amp' );
			ampMenuItem.remove();
		}
	} );
}
