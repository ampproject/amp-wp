/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { isNonAmpWindow, isAmpWindow } from './utils';

const { parent, ampPairedBrowsingClientData } = window;
const { ampUrl, nonAmpUrl, isAmpDocument } = ampPairedBrowsingClientData;

/**
 * Modify document for paired browsing.
 */
function modifyDocumentForPairedBrowsing() {
	// Scrolling is not synchronized if `scroll-behavior` is set to `smooth`.
	document.documentElement.style.setProperty( 'scroll-behavior', 'auto', 'important' );

	if ( isAmpDocument ) {
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
		// No need to show the AMP menu in the Non-AMP window.
		const ampMenuItem = document.getElementById( 'wp-admin-bar-amp' );
		ampMenuItem.remove();
	}
}

/**
 * Send message to app.
 *
 * @param {Window} win  Window.
 * @param {string} type Type.
 * @param {Object} data Data.
 */
function sendMessage( win, type, data ) {
	win.postMessage(
		{
			type,
			...data,
			ampPairedBrowsing: true,
		},
		nonAmpUrl, // Because the paired browsing app is accessed via the canonical URL.
	);
}

/**
 * Receive message.
 *
 * @param {MessageEvent} event
 */
function receiveMessage( event ) {
	if ( ! event.data || ! event.data.ampPairedBrowsing || ! event.data.type || ! event.source ) {
		return;
	}
	switch ( event.data.type ) {
		case 'scroll':
			receiveScroll( event.data );
			break;
		case 'replaceLocation':
			receiveReplaceLocation( event.data );
			break;
		default:
	}
}

/**
 * Send scroll.
 */
function sendScroll() {
	sendMessage(
		parent,
		'scroll',
		{
			x: window.scrollX,
			y: window.scrollY,
		},
	);
}

/**
 * Receive scroll.
 *
 * @param {Object} data
 * @param {number} data.x
 * @param {number} data.y
 */
function receiveScroll( { x, y } ) {
	window.scrollTo( x, y );
}

/**
 * Receive replace location.
 *
 * @param {string} href
 */
function receiveReplaceLocation( { href } ) {
	window.location.replace( href );
}

/**
 * Send heartbeat.
 *
 * @see https://github.com/WordPress/wordpress-develop/blob/7a16c4d5809507bbfa9eb0f95178092492b04727/src/js/_enqueues/wp/customize/controls.js#L6679-L6727
 */
function sendHeartbeat() {
	sendMessage(
		parent,
		'heartbeat',
		{
			isAmpDocument,
			ampUrl,
			nonAmpUrl,
			documentTitle: document.title,
		},
	);
}

if ( isNonAmpWindow( window ) || isAmpWindow( window ) ) {
	domReady( () => {
		modifyDocumentForPairedBrowsing();

		window.addEventListener( 'message', receiveMessage );
		window.addEventListener( 'scroll', sendScroll, { passive: true } );

		sendHeartbeat();
		setInterval( sendHeartbeat, 1000 );
	} );
}
