/**
 * WordPress dependencies
 */
import { addQueryArgs, removeQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import './app.css';

const { ampPairedBrowsingAppData, history } = window;
const {
	noampQueryVar,
	noampMobile,
	ampPairedBrowsingQueryVar,
	documentTitlePrefix,
} = ampPairedBrowsingAppData;

class PairedBrowsingApp {
	/**
	 * Disconnected client.
	 *
	 * @type {HTMLIFrameElement}
	 */
	disconnectedClient;

	/**
	 * AMP IFrame
	 *
	 * @type {HTMLIFrameElement}
	 */
	ampIframe;

	/**
	 * Timestamp when the AMP iframe last sent a heartbeat.
	 *
	 * @type {number}
	 */
	ampHeartbeatTimestamp = Date.now();

	/**
	 * Non-AMP IFrame
	 *
	 * @type {HTMLIFrameElement}
	 */
	nonAmpIframe;

	/**
	 * Timestamp when the non-AMP iframe last sent a heartbeat.
	 *
	 * @type {number}
	 */
	nonAmpHeartbeatTimestamp = Date.now();

	/**
	 * Current AMP URL.
	 *
	 * @type {string}
	 */
	currentAmpUrl;

	/**
	 * The most recent URL that was being navigated to in the AMP window.
	 *
	 * @type {?string}
	 */
	navigateAmpUrl;

	/**
	 * Current non-AMP URL.
	 *
	 * @type {string}
	 */
	currentNonAmpUrl;

	/**
	 * The most recent URL that was being navigated to in the non-AMP window.
	 *
	 * @type {?string}
	 */
	navigateNonAmpUrl;

	/**
	 * Non-AMP Link
	 *
	 * @type {HTMLAnchorElement}
	 */
	nonAmpLink;

	/**
	 * AMP Link
	 *
	 * @type {HTMLAnchorElement}
	 */
	ampLink;

	/**
	 * Active iframe.
	 *
	 * @type {?HTMLIFrameElement}
	 */
	activeIframe;

	/**
	 * Constructor.
	 */
	constructor() {
		this.nonAmpIframe = document.querySelector( '#non-amp iframe' );
		this.ampIframe = document.querySelector( '#amp iframe' );

		this.currentNonAmpUrl = this.nonAmpIframe.src;
		this.currentAmpUrl = this.ampIframe.src;

		// Link to exit paired browsing.
		this.nonAmpLink = /** @type {HTMLAnchorElement} */ document.getElementById( 'non-amp-link' );
		this.ampLink = /** @type {HTMLAnchorElement} */ document.getElementById( 'amp-link' );

		// Overlay that is displayed on the client that becomes disconnected.
		this.disconnectOverlay = document.querySelector( '.disconnect-overlay' );
		this.disconnectButtons = {
			exit: document.querySelector( '.disconnect-overlay .button.exit' ),
			goBack: document.querySelector( '.disconnect-overlay .button.go-back' ),
		};
		this.addDisconnectButtonListeners();

		global.addEventListener( 'message', ( event ) => {
			this.receiveMessage( event );
		} );

		// Set the active iframe based on which got the last mouseenter.
		// Note that setting activeIframe may get set by receiveScroll if the user starts scrolling
		// before moving the mouse.
		document.getElementById( 'non-amp' ).addEventListener( 'mouseenter', () => {
			this.activeIframe = this.nonAmpIframe;
		} );
		document.getElementById( 'amp' ).addEventListener( 'mouseenter', () => {
			this.activeIframe = this.ampIframe;
		} );

		// Load clients.
		Promise.all( this.getIframeLoadedPromises() ).then( () => {
			setInterval(
				() => {
					this.checkConnectedClients();
				},
				100,
			);
		} );
	}

	/**
	 * Return whether the window is for the AMP page.
	 *
	 * @param {Window} win Window.
	 * @return {boolean} Whether AMP window.
	 */
	isAmpWindow( win ) {
		return win === this.ampIframe.contentWindow;
	}

	/**
	 * Return whether the window is for the non-AMP page.
	 *
	 * @param {Window} win Window.
	 * @return {boolean} Whether non-AMP window.
	 */
	isNonAmpWindow( win ) {
		return win === this.nonAmpIframe.contentWindow;
	}

	/**
	 * Send message to app.
	 *
	 * @param {Window} win  Window.
	 * @param {string} type Type.
	 * @param {Object} data Data.
	 */
	sendMessage( win, type, data = {} ) {
		win.postMessage(
			{
				type,
				...data,
				ampPairedBrowsing: true,
			},
			this.isAmpWindow( win ) ? this.currentAmpUrl : this.currentNonAmpUrl,
		);
	}

	/**
	 * Receive message.
	 *
	 * @param {MessageEvent} event
	 */
	receiveMessage( event ) {
		if ( ! event.data || ! event.data.type || ! event.data.ampPairedBrowsing || ! event.source ) {
			return;
		}

		if ( ! this.isAmpWindow( event.source ) && ! this.isNonAmpWindow( event.source ) ) {
			return;
		}

		switch ( event.data.type ) {
			case 'loaded':
				this.receiveLoaded( event.data, event.source );
				break;
			case 'scroll':
				this.receiveScroll( event.data, event.source );
				break;
			case 'heartbeat':
				this.receiveHeartbeat( event.source );
				break;
			case 'navigate':
				this.receiveNavigate( event.data, event.source );
				break;
			default:
		}
	}

	/**
	 * Return promises to load iframes asynchronously.
	 *
	 * @return {Promise<void>[]} Promises that determine if the iframes are loaded.
	 */
	getIframeLoadedPromises() {
		return [
			new Promise( ( resolve ) => {
				this.nonAmpIframe.addEventListener( 'load', resolve );
			} ),
			new Promise( ( resolve ) => {
				this.ampIframe.addEventListener( 'load', resolve );
			} ),
		];
	}

	/**
	 * Receive heartbeat.
	 *
	 * @param {Window} sourceWindow The source window.
	 */
	receiveHeartbeat( sourceWindow ) {
		if ( this.isAmpWindow( sourceWindow ) ) {
			this.ampHeartbeatTimestamp = Date.now();
		} else {
			this.nonAmpHeartbeatTimestamp = Date.now();
		}
	}

	/**
	 * Receive navigate.
	 *
	 * @param {Object} data         Data.
	 * @param {string} data.href    Href.
	 * @param {Window} sourceWindow The source window.
	 */
	receiveNavigate( { href }, sourceWindow ) {
		if ( this.isAmpWindow( sourceWindow ) ) {
			this.navigateAmpUrl = href;
		} else {
			this.navigateNonAmpUrl = href;
		}
	}

	/**
	 * Check connected clients.
	 */
	checkConnectedClients() {
		this.sendMessage( this.ampIframe.contentWindow, 'init' );
		this.sendMessage( this.nonAmpIframe.contentWindow, 'init' );

		if ( ! this.isClientConnected( this.ampIframe ) ) {
			this.showDisconnectOverlay( this.ampIframe );
		} else if ( ! this.isClientConnected( this.nonAmpIframe ) ) {
			this.showDisconnectOverlay( this.nonAmpIframe );
		} else {
			this.disconnectOverlay.classList.remove( 'disconnected' );
		}
	}

	/**
	 * Add event listeners for buttons on disconnect overlay.
	 */
	addDisconnectButtonListeners() {
		// The 'Go back' button goes back to the previous page of the parent window.
		this.disconnectButtons.goBack.addEventListener( 'click', () => {
			window.history.back();
		} );
	}

	/**
	 * Shows the 'disconnected' overlay for the supplied iframe.
	 *
	 * @param {HTMLIFrameElement} iframe The iframe that hosts the paired browsing client.
	 */
	showDisconnectOverlay( iframe ) {
		// Show the exit link if we know the URL that the user was last trying to go to.
		const navigateUrl = this.ampIframe === iframe ? this.navigateAmpUrl : this.navigateNonAmpUrl;
		if ( navigateUrl ) {
			this.disconnectButtons.exit.hidden = false;
			this.disconnectButtons.exit.href = navigateUrl;
		} else {
			this.disconnectButtons.exit.hidden = true;
		}

		// Show the 'Go Back' button if the parent window has history.
		this.disconnectButtons.goBack.classList.toggle( 'hidden', 0 >= window.history.length );

		// Applying the 'amp' class will overlay it on the AMP iframe.
		this.disconnectOverlay.classList.toggle(
			'amp',
			this.ampIframe === iframe,
		);

		this.disconnectOverlay.classList.add( 'disconnected' );
	}

	/**
	 * Determines the status of the paired browsing client in an iframe.
	 *
	 * @param {HTMLIFrameElement} iframe The iframe.
	 * @return {boolean} Whether the client is connected.
	 */
	isClientConnected( iframe ) {
		const threshold = 2000;
		if ( iframe === this.ampIframe ) {
			return Date.now() - this.ampHeartbeatTimestamp < threshold;
		}
		return Date.now() - this.nonAmpHeartbeatTimestamp < threshold;
	}

	/**
	 * Purge removable query vars from the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL without any AMP related query variables.
	 */
	purgeRemovableQueryVars( url ) {
		return removeQueryArgs( url, noampQueryVar, ampPairedBrowsingQueryVar );
	}

	/**
	 * Adds the AMP paired browsing query variable to the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL with the AMP paired browsing query variable.
	 */
	addPairedBrowsingQueryVar( url ) {
		return addQueryArgs(
			url,
			{
				[ ampPairedBrowsingQueryVar ]: '1',
			},
		);
	}

	/**
	 * Removes the URL hash from the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL without the hash.
	 */
	removeUrlHash( url ) {
		const parsedUrl = new URL( url );
		parsedUrl.hash = '';
		return parsedUrl.href;
	}

	/**
	 * Replace location.
	 *
	 * @param {HTMLIFrameElement} iframe IFrame Element.
	 * @param {string}            url    URL.
	 */
	replaceLocation( iframe, url ) {
		this.sendMessage(
			iframe.contentWindow,
			'replaceLocation',
			{ href: url },
		);
	}

	/**
	 * Receive scroll.
	 *
	 * @param {Object} data         Data.
	 * @param {number} data.x       X position.
	 * @param {number} data.y       Y position.
	 * @param {Window} sourceWindow The source window.
	 */
	receiveScroll( { x, y }, sourceWindow ) {
		// Rely on scroll event to determine initially-active iframe before mouse first moves.
		if ( ! this.activeIframe ) {
			this.activeIframe = this.isAmpWindow( sourceWindow )
				? this.ampIframe
				: this.nonAmpIframe;
		}

		// Ignore scroll events from the non-active iframe.
		if ( ! this.activeIframe || sourceWindow !== this.activeIframe.contentWindow ) {
			return;
		}

		const otherWindow = this.isAmpWindow( sourceWindow )
			? this.nonAmpIframe.contentWindow
			: this.ampIframe.contentWindow;
		this.sendMessage( otherWindow, 'scroll', { x, y } );
	}

	/**
	 * Receive loaded.
	 *
	 * @param {Object}  data                   Data.
	 * @param {boolean} data.isAmpDocument     Whether the document is actually an AMP page.
	 * @param {?string} data.ampUrl            The AMP URL.
	 * @param {?string} data.nonAmpUrl         The non-AMP URL.
	 * @param {string}  data.documentTitle The canonical link URL if present.
	 * @param {Window}  sourceWindow The source window.
	 */
	receiveLoaded( { isAmpDocument, ampUrl, nonAmpUrl, documentTitle }, sourceWindow ) {
		const isAmpSource = this.isAmpWindow( sourceWindow );
		const sourceIframe = isAmpSource ? this.ampIframe : this.nonAmpIframe;

		if ( isAmpSource ) {
			// Force the AMP iframe to always have an AMP URL.
			if ( ! isAmpDocument ) {
				this.replaceLocation( sourceIframe, ampUrl );
				return;
			}

			this.currentAmpUrl = ampUrl;

			// Update the AMP link above the iframe used for exiting paired browsing.
			this.ampLink.href = removeQueryArgs( ampUrl, noampQueryVar );
		} else {
			// Force the non-AMP iframe to always have a non-AMP URL.
			if ( isAmpDocument ) {
				this.replaceLocation( sourceIframe, nonAmpUrl );
				return;
			}

			this.currentNonAmpUrl = nonAmpUrl;

			// Update the non-AMP link above the iframe used for exiting paired browsing.
			this.nonAmpLink.href = addQueryArgs(
				nonAmpUrl,
				{ [ noampQueryVar ]: noampMobile },
			);
		}

		// Make sure the opposite iframe is set to match.
		const thisCurrentUrl = isAmpSource ? nonAmpUrl : ampUrl;
		const otherCurrentUrl = isAmpSource ? this.currentNonAmpUrl : this.currentAmpUrl;

		if (
			this.purgeRemovableQueryVars( this.removeUrlHash( thisCurrentUrl ) ) !==
			this.purgeRemovableQueryVars( this.removeUrlHash( otherCurrentUrl ) )
		) {
			const url = isAmpSource
				? nonAmpUrl
				: ampUrl;

			this.replaceLocation(
				isAmpSource ? this.nonAmpIframe : this.ampIframe,
				this.purgeRemovableQueryVars( url ),
			);
			return;
		}

		document.title = documentTitlePrefix + ' ' + documentTitle;

		history.replaceState(
			{},
			'',
			this.addPairedBrowsingQueryVar( this.purgeRemovableQueryVars( nonAmpUrl ) ),
		);
	}
}

window.pairedBrowsingApp = new PairedBrowsingApp();
