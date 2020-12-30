/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const { parent, ampPairedBrowsingClientData } = window;
const { ampUrl, nonAmpUrl, isAmpDocument } = ampPairedBrowsingClientData;

const nonAmpUrlObject = new URL( nonAmpUrl );

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
function sendMessage( win, type, data = {} ) {
	win.postMessage(
		{
			type,
			...data,
			ampPairedBrowsing: true,
		},
		nonAmpUrlObject.origin, // Because the paired browsing app is accessed via the canonical URL.
	);
}

let initialized = false;

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
		case 'init':
			if ( ! initialized ) {
				initialized = true;
				receiveInit( event.data );
			}
			break;
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
 * Handle click event.
 *
 * @param {MouseEvent} event
 */
function handleClick( event ) {
	const element = event.target;
	const link = element.matches( '[href]' ) ? element : element.closest( '[href]' );
	if ( link ) {
		sendMessage(
			parent,
			'navigate',
			{ href: link.href },
		);
	}
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
 * Send loaded.
 */
function sendLoaded() {
	sendMessage(
		parent,
		'loaded',
		{
			isAmpDocument,
			ampUrl,
			nonAmpUrl,
			documentTitle: document.title,
		},
	);
}

/**
 * Send heartbeat.
 */
function sendHeartbeat() {
	sendMessage( parent, 'heartbeat' );
}

/**
 * Receive init.
 */
function receiveInit() {
	sendHeartbeat();
	setInterval( sendHeartbeat, 500 );

	global.document.addEventListener( 'click', handleClick, { passive: true } );
	global.addEventListener( 'scroll', sendScroll, { passive: true } );
	domReady( modifyDocumentForPairedBrowsing );

	sendLoaded();
}

global.addEventListener( 'message', receiveMessage );
