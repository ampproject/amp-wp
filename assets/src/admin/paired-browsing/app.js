/**
 * WordPress dependencies
 */
import { addQueryArgs, hasQueryArg, removeQueryArgs } from '@wordpress/url';
/**
 * Internal dependencies
 */
import './app.css';

const { app, history } = window;
const { noampQueryVar, noampMobile, ampPairedBrowsingQueryVar, documentTitlePrefix } = app;

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
	 * Non-AMP IFrame
	 *
	 * @type {HTMLIFrameElement}
	 */
	nonAmpIframe;

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
	 * Constructor.
	 */
	constructor() {
		this.nonAmpIframe = document.querySelector( '#non-amp iframe' );
		this.ampIframe = document.querySelector( '#amp iframe' );
		this.ampPageHasErrors = false;

		// Link to exit paired browsing.
		this.nonAmpLink = /** @type {HTMLAnchorElement} */ document.getElementById( 'non-amp-link' );
		this.ampLink = /** @type {HTMLAnchorElement} */ document.getElementById( 'amp-link' );

		// Overlay that is displayed on the client that becomes disconnected.
		this.disconnectOverlay = document.querySelector( '.disconnect-overlay' );
		this.disconnectText = {
			general: document.querySelector( '.disconnect-overlay .dialog-text span.general' ),
			invalidAmp: document.querySelector( '.disconnect-overlay .dialog-text span.invalid-amp' ),
		};
		this.disconnectButtons = {
			exit: document.querySelector( '.disconnect-overlay .button.exit' ),
			goBack: document.querySelector( '.disconnect-overlay .button.go-back' ),
		};
		this.addDisconnectButtonListeners();

		// Load clients.
		Promise.all( this.getIframeLoadedPromises() );
	}

	/**
	 * Add event listeners for buttons on disconnect overlay.
	 */
	addDisconnectButtonListeners() {
		// The 'Exit' button navigates the parent window to the URL of the disconnected client.
		this.disconnectButtons.exit.addEventListener( 'click', () => {
			window.location.assign( this.disconnectedClient.contentWindow.location.href );
		} );

		// The 'Go back' button goes back to the previous page of the parent window.
		this.disconnectButtons.goBack.addEventListener( 'click', () => {
			window.history.back();
		} );
	}

	/**
	 * Return promises to load iframes asynchronously.
	 *
	 * @return {Promise<void>[]} Promises that determine if the iframes are loaded.
	 */
	getIframeLoadedPromises() {
		return [
			new Promise( ( resolve ) => {
				this.nonAmpIframe.addEventListener( 'load', () => {
					this.toggleDisconnectOverlay( this.nonAmpIframe );
					resolve();
				} );
			} ),

			new Promise( ( resolve ) => {
				this.ampIframe.addEventListener( 'load', () => {
					this.toggleDisconnectOverlay( this.ampIframe );
					resolve();
				} );
			} ),
		];
	}

	/**
	 * Validates whether or not the window document is AMP compatible.
	 *
	 * @param {Document} doc Window document.
	 * @return {boolean} True if AMP compatible, false if not.
	 */
	documentIsAmp( doc ) {
		return doc.querySelector( 'head > script[src="https://cdn.ampproject.org/v0.js"]' );
	}

	/**
	 * Toggles the 'disconnected' overlay for the supplied iframe.
	 *
	 * @param {HTMLIFrameElement} iframe The iframe that hosts the paired browsing client.
	 */
	toggleDisconnectOverlay( iframe ) {
		const isClientConnected = this.isClientConnected( iframe );

		if ( ! isClientConnected ) {
			if ( this.ampIframe === iframe && this.ampPageHasErrors ) {
				this.disconnectText.general.classList.toggle( 'hidden', true );
				this.disconnectText.invalidAmp.classList.toggle( 'hidden', false );
			} else {
				this.disconnectText.general.classList.toggle( 'hidden', false );
				this.disconnectText.invalidAmp.classList.toggle( 'hidden', true );
			}

			// Show the 'Go Back' button if the parent window has history.
			this.disconnectButtons.goBack.classList.toggle( 'hidden', 0 >= window.history.length );
			// If the document is not available, the window URL cannot be accessed.
			this.disconnectButtons.exit.classList.toggle( 'hidden', null === iframe.contentDocument );

			this.disconnectedClient = iframe;
		}

		// Applying the 'amp' class will overlay it on the AMP iframe.
		this.disconnectOverlay.classList.toggle(
			'amp',
			! isClientConnected && this.ampIframe === iframe,
		);

		this.disconnectOverlay.classList.toggle(
			'disconnected',
			! isClientConnected,
		);
	}

	/**
	 * Determines the status of the paired browsing client in an iframe.
	 *
	 * @param {HTMLIFrameElement} iframe The iframe.
	 */
	isClientConnected( iframe ) {
		if ( this.ampIframe === iframe && this.ampPageHasErrors ) {
			return false;
		}

		return null !== iframe.contentWindow &&
			null !== iframe.contentDocument &&
			true === iframe.contentWindow.ampPairedBrowsingClient;
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
	 * Checks if a URL has the 'amp_validation_errors' query variable.
	 *
	 * @param {string} url URL string.
	 * @return {boolean} True if such query var exists, false if not.
	 */
	urlHasValidationErrorQueryVar( url ) {
		return hasQueryArg( url, 'amp_validation_errors' );
	}

	/**
	 * Get non-AMP location from a window.
	 *
	 * @param {Window} win Window.
	 * @return {string|null} Non-AMP location.
	 */
	getWindowNonAmpLocation( win ) {
		if ( this.documentIsAmp( win.document ) ) {
			const canonicalLink = win.document.querySelector( 'head > link[rel=canonical]' );
			return canonicalLink ? canonicalLink.href : null;
		}
		return win.location.href;
	}

	/**
	 * Registers the provided client window with its parent, so that it can be managed by it.
	 *
	 * @param {Window} win Document window.
	 */
	registerClientWindow( win ) {
		let oppositeWindow;
		const isAmp = this.documentIsAmp( win.document );

		const amphtmlLink = win.document.querySelector( 'head > link[rel=amphtml]' );
		const canonicalLink = win.document.querySelector( 'head > link[rel=canonical]' );

		let ampUrl, nonAmpUrl;
		if ( isAmp ) {
			ampUrl = win.location.href;
			nonAmpUrl = canonicalLink ? canonicalLink.href : null;
		} else {
			nonAmpUrl = win.location.href;
			ampUrl = amphtmlLink ? amphtmlLink.href : null;
		}

		if ( win === this.ampIframe.contentWindow ) {
			if ( ! isAmp ) {
				if ( this.urlHasValidationErrorQueryVar( win.location.href ) ) {
					/*
					 * If the AMP page has validation errors, mark the page as invalid so that the
					 * 'disconnected' overlay can be shown.
					 */
					this.ampPageHasErrors = true;
					this.toggleDisconnectOverlay( this.ampIframe );
					return;
				} else if ( ampUrl ) {
					// Force the AMP iframe to always have an AMP URL, if an AMP version is available.
					win.location.replace( ampUrl );
					return;
				}

				/*
				 * If the AMP iframe has loaded a non-AMP page and none of the conditions above are
				 * true, then explicitly mark it as having errors and display the 'disconnected
				 * overlay.
				 */
				this.ampPageHasErrors = true;
				this.toggleDisconnectOverlay( this.ampIframe );
				return;
			}

			// Update the AMP link above the iframe used for exiting paired browsing.
			this.ampLink.href = removeQueryArgs( this.ampIframe.contentWindow.location.href, noampQueryVar );

			this.ampPageHasErrors = false;
			oppositeWindow = this.nonAmpIframe.contentWindow;
		} else {
			// Force the non-AMP iframe to always have a non-AMP URL.
			if ( isAmp ) {
				win.location.replace( this.purgeRemovableQueryVars( nonAmpUrl ) );
				return;
			}

			// Update the non-AMP link above the iframe used for exiting paired browsing.
			this.nonAmpLink.href = addQueryArgs(
				this.nonAmpIframe.contentWindow.location.href,
				{ [ noampQueryVar ]: noampMobile },
			);

			oppositeWindow = this.ampIframe.contentWindow;
		}

		// Synchronize scrolling from current window to its opposite.
		win.addEventListener(
			'scroll',
			() => {
				if ( oppositeWindow && oppositeWindow.ampPairedBrowsingClient && oppositeWindow.scrollTo ) {
					oppositeWindow.scrollTo( win.scrollX, win.scrollY );
				}
			},
			{ passive: true },
		);

		// Scrolling is not synchronized if `scroll-behavior` is set to `smooth`.
		win.document.documentElement.style.setProperty( 'scroll-behavior', 'auto', 'important' );

		// Make sure the opposite iframe is set to match.
		if (
			oppositeWindow &&
			oppositeWindow.location &&
			(
				this.purgeRemovableQueryVars( this.removeUrlHash( this.getWindowNonAmpLocation( oppositeWindow ) ) ) !==
				this.purgeRemovableQueryVars( this.removeUrlHash( this.getWindowNonAmpLocation( win ) ) )
			)
		) {
			const url = oppositeWindow === this.ampIframe.contentWindow
				? ampUrl
				: nonAmpUrl;

			oppositeWindow.location.replace( url );

			return;
		}

		document.title = documentTitlePrefix + ' ' + win.document.title;

		history.replaceState(
			{},
			'',
			this.addPairedBrowsingQueryVar( this.purgeRemovableQueryVars( win.location.href ) ),
		);
	}
}

window.pairedBrowsingApp = new PairedBrowsingApp();
