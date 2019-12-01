/**
 * WordPress dependencies
 */
// import domReady from '@wordpress/dom-ready';

function domReady( callback ) {
	if (
		document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
		document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
	) {
		return callback();
	} // DOMContentLoaded has not fired yet, delay callback until then.

	document.addEventListener( 'DOMContentLoaded', callback );
	return null;
}

const { parent } = window;

function documentIsAmp() {
	return Boolean( document.querySelector( 'head > script[src="https://cdn.ampproject.org/v0.js"]' ) );
}

function isEmbedded() {
	return parent && parent.pairedBrowsingApp; // @todo Also consider window.portalHost.
}

function checkWhetherClient() {
	if ( parent.pairedBrowsingApp ) { // @todo This is always false! Portals can't access their parent window currently. May need to use message passing?
		window.ampPairedBrowsingClient = true;
		parent.registerClientWindow( window );
	} else {
		window.ampPairedBrowsingClient = false;
	}
	updateAdminBarLinks();
}

checkWhetherClient();
window.addEventListener( 'portalactivate', checkWhetherClient );
window.addEventListener( 'message', ( event ) => {
	if ( 'ampPairedBrowsingEmbedded' === event.data ) {
		checkWhetherClient();
	}
} );

function updateAdminBarLinks() {
	if ( documentIsAmp() ) {
		// Hide the paired browsing menu item.
		const pairedBrowsingMenuItem = document.getElementById( 'wp-admin-bar-amp-paired-browsing' );
		if ( pairedBrowsingMenuItem ) {
			pairedBrowsingMenuItem.hidden = isEmbedded();
		}

		// Hide menu item to view non-AMP version.
		const ampViewBrowsingItem = document.getElementById( 'wp-admin-bar-amp-view' );
		if ( ampViewBrowsingItem ) {
			ampViewBrowsingItem.hidden = isEmbedded();
		}
	} else {
		/**
		 * No need to show the AMP menu in the Non-AMP window.
		 */
		const ampMenuItem = document.getElementById( 'wp-admin-bar-amp' );
		ampMenuItem.hidden = isEmbedded();
	}
}

domReady( () => {
	updateAdminBarLinks();

	const pairedBrowsingLink = document.querySelector( '#wp-admin-bar-amp-paired-browsing > a' );

	// If portals are available, let clicking the link open the portal.
	if ( pairedBrowsingLink && 'HTMLPortalElement' in window ) {
		pairedBrowsingLink.addEventListener( 'click', ( event ) => {
			event.preventDefault();

			const portal = document.createElement( 'portal' );
			portal.src = pairedBrowsingLink.href;
			document.body.appendChild( portal );

			portal.addEventListener( 'load', () => {
				portal.activate( {
					data: {
						isAmp: documentIsAmp(),
					},
				} );
			} );
		} );
	}
} );
