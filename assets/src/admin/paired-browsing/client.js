const { parent } = window;

if ( parent.pairedBrowsingApp ) {
	window.ampPairedBrowsingClient = true;
	const app = parent.pairedBrowsingApp;

	app.registerClientWindow( window );

	document.addEventListener( 'DOMContentLoaded', () => {
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
			 * Override the entire AMP menu item with just "Non-AMP". There should be no link to
			 * the AMP version since it is already being shown.
			 */
			const ampMenuItem = document.getElementById( 'wp-admin-bar-amp' );
			if ( ampMenuItem ) {
				ampMenuItem.innerHTML = 'Non-AMP';
			}
		}
	} );
}
