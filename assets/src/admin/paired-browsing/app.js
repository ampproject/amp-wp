/* global HTMLPortalElement */

/**
 * WordPress dependencies
 */
import { addQueryArgs, hasQueryArg, removeQueryArgs } from '@wordpress/url';
/**
 * Internal dependencies
 */
import './app.css';

const { app, history } = window;
const { ampSlug, ampPairedBrowsingQueryVar, ampValidationErrorsQueryVar, documentTitlePrefix, ampFrameTitle, nonAmpFrameTitle } = app;

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
	 * @type {HTMLIFrameElement|HTMLPortalElement}
	 */
	ampFrame;

	/**
	 * Non-AMP IFrame
	 *
	 * @type {HTMLIFrameElement|HTMLPortalElement}
	 */
	nonAmpFrame;

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
		this.initializeFrames();
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
	 * @return {[Promise<Function>, Promise<Function>]} Promises that determine if the iframes are loaded.
	 */
	initializeFrames() {
		const sandbox = 'allow-forms allow-scripts allow-same-origin allow-popups';

		if ( 'HTMLPortalElement' in window ) {
			throw new Error( 'Not implemented' );
		} else {
			this.nonAmpFrame = document.createElement( 'iframe' );
			this.nonAmpFrame.setAttribute( 'sandbox', sandbox );
			this.nonAmpFrame.src = this.nonAmpLink.href;
			this.nonAmpFrame.title = nonAmpFrameTitle;
			document.getElementById( 'non-amp' ).appendChild( this.nonAmpFrame );

			this.ampFrame = document.createElement( 'iframe' );
			this.ampFrame.setAttribute( 'sandbox', sandbox );
			this.ampFrame.src = this.ampLink.href;
			this.ampFrame.title = ampFrameTitle;
			document.getElementById( 'amp' ).appendChild( this.ampFrame );
		}

		return [
			new Promise( ( resolve ) => {
				this.nonAmpFrame.addEventListener( 'load', () => {
					this.toggleDisconnectOverlay( this.nonAmpFrame );
					resolve();
				} );
			} ),

			new Promise( ( resolve ) => {
				this.ampFrame.addEventListener( 'load', () => {
					this.toggleDisconnectOverlay( this.ampFrame );
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
			if ( this.ampFrame === iframe && this.ampPageHasErrors ) {
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
			! isClientConnected && this.ampFrame === iframe,
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
		if ( this.ampFrame === iframe && this.ampPageHasErrors ) {
			return false;
		}

		return null !== iframe.contentWindow &&
			null !== iframe.contentDocument &&
			true === iframe.contentWindow.ampPairedBrowsingClient;
	}

	/**
	 * Removes AMP related query variables from the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL without any AMP related query variables.
	 */
	removeAmpQueryVars( url ) {
		return removeQueryArgs( url, ampSlug, ampPairedBrowsingQueryVar, ampValidationErrorsQueryVar );
	}

	/**
	 * Adds the AMP query variable to the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL with the AMP query variable.
	 */
	addAmpQueryVar( url ) {
		return addQueryArgs(
			url,
			{
				[ ampSlug ]: '',
			},
		);
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
	 * Registers the provided client window with its parent, so that it can be managed by it.
	 *
	 * @param {Window} win Document window.
	 */
	registerClientWindow( win ) {
		let oppositeWindow;

		if ( win === this.ampFrame.contentWindow ) {
			if ( ! this.documentIsAmp( win.document ) ) {
				if ( this.urlHasValidationErrorQueryVar( win.location.href ) ) {
					/*
					 * If the AMP page has validation errors, mark the page as invalid so that the
					 * 'disconnected' overlay can be shown.
					 */
					this.ampPageHasErrors = true;
					this.toggleDisconnectOverlay( this.ampFrame );
					return;
				} else if ( win.document.querySelector( 'head > link[rel=amphtml]' ) ) {
					// Force the AMP iframe to always have an AMP URL, if an AMP version is available.
					win.location.replace( this.addAmpQueryVar( win.location.href ) );
					return;
				}

				/*
				 * If the AMP iframe has loaded a non-AMP page and none of the conditions above are
				 * true, then explicitly mark it as having errors and display the 'disconnected
				 * overlay.
				 */
				this.ampPageHasErrors = true;
				this.toggleDisconnectOverlay( this.ampFrame );
				return;
			}

			// Update the AMP link above the iframe used for exiting paired browsing.
			this.ampLink.href = this.ampFrame.contentWindow.location.href;

			this.ampPageHasErrors = false;
			oppositeWindow = this.nonAmpFrame.contentWindow;
		} else {
			// Force the non-AMP iframe to always have a non-AMP URL.
			if ( this.documentIsAmp( win.document ) ) {
				win.location.replace( this.removeAmpQueryVars( win.location.href ) );
				return;
			}

			// Update the non-AMP link above the iframe used for exiting paired browsing.
			this.nonAmpLink.href = this.nonAmpFrame.contentWindow.location.href;

			oppositeWindow = this.ampFrame.contentWindow;
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

		// Make sure the opposite iframe is set to match.
		if (
			oppositeWindow &&
			oppositeWindow.location &&
			(
				this.removeAmpQueryVars( this.removeUrlHash( oppositeWindow.location.href ) ) !==
				this.removeAmpQueryVars( this.removeUrlHash( win.location.href ) )
			)
		) {
			const url = oppositeWindow === this.ampFrame.contentWindow ?
				this.addAmpQueryVar( win.location.href ) :
				this.removeAmpQueryVars( win.location.href );

			oppositeWindow.location.replace( url );

			return;
		}

		document.title = documentTitlePrefix + ' ' + win.document.title;

		history.replaceState(
			{},
			'',
			this.addPairedBrowsingQueryVar( this.removeAmpQueryVars( win.location.href ) ),
		);
	}
}

window.pairedBrowsingApp = new PairedBrowsingApp();
