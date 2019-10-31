/**
 * WordPress dependencies
 */
import { removeQueryArgs, addQueryArgs, hasQueryArg } from '@wordpress/url';

/**
 * Internal dependencies
 */
import './app.css';

const { app, alert, history } = window;
const { ampSlug, ampPairedBrowsingQueryVar } = app;

class PairedBrowsingApp {
	/**
	 * Set the iframes on init.
	 */
	constructor() {
		this.nonAmpIframe = document.getElementById( 'non-amp' ).firstElementChild;
		this.ampIframe = document.getElementById( 'amp' ).firstElementChild;

		Promise.all( this.getIframeLoadedPromises() ).then( () => {
			// Check every second to see if any iframe has disconnected.
			setInterval( () => this.checkConnectedIframes(), 1000 );

			const exitLink = document.getElementById( 'exit-link' );

			exitLink.addEventListener( 'click', () => {
				// Let's head back to the non-AMP page upon exiting.
				window.location.replace( this.nonAmpIframe.contentWindow.location.href );
			} );
		} );
	}

	/**
	 * Determine whether both iframes have been loaded successfully.
	 *
	 * @return {[Promise<Function>, Promise<Function>]} Promises which will determine if the iframes are loaded.
	 */
	getIframeLoadedPromises() {
		return [
			new Promise( ( resolve, reject ) => {
				this.nonAmpIframe.addEventListener( 'load', resolve );
				setTimeout( reject, 5000 );
			} ),
			new Promise( ( resolve, reject ) => {
				this.ampIframe.addEventListener( 'load', resolve );
				setTimeout( reject, 5000 );
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
		return doc.documentElement.hasAttribute( 'amp' ) || doc.documentElement.hasAttribute( '⚡️' );
	}

	/**
	 * Toggles the 'disconnected' class on the iframe, for whether or not the client in the iframe
	 * is detected.
	 */
	checkConnectedIframes() {
		this.ampIframe.classList.toggle(
			'disconnected',
			! ( this.nonAmpIframe.contentWindow && this.nonAmpIframe.contentWindow.ampPairedBrowsingClient ),
		);

		this.nonAmpIframe.classList.toggle(
			'disconnected',
			! ( this.ampIframe.contentWindow && this.ampIframe.contentWindow.ampPairedBrowsingClient ),
		);
	}

	/**
	 * Removes AMP related query variables from the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL without any AMP related query variables.
	 */
	removeAmpQueryVars( url ) {
		return removeQueryArgs( url, ampSlug, 'amp_validation_errors' );
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
				ampSlug: '',
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

		if ( win === this.ampIframe.contentWindow ) {
			if ( ! this.documentIsAmp( win.document ) ) {
				if ( this.urlHasValidationErrorQueryVar( win.location.href ) ) {
					// eslint-disable-next-line no-alert
					alert( 'The AMP version of this page could not be rendered due to validation errors.' );

					this.ampIframe.classList.toggle( 'disconnected', true );
				} else if ( win.document.querySelector( 'head > link[rel=amphtml]' ) ) {
					// Force the AMP iframe to always have an AMP URL, if an AMP version is available.
					win.location.replace( this.addAmpQueryVar( win.location.href ) );
					return;
				}
			}

			oppositeWindow = this.nonAmpIframe.contentWindow;
		} else {
			// Force the non-AMP iframe to always have a non-AMP URL.
			if ( this.documentIsAmp( win.document ) ) {
				win.location.replace( this.removeAmpQueryVars( win.location.href ) );
				return;
			}

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

		// Make sure the opposite iframe is set to match.
		if (
			oppositeWindow &&
			oppositeWindow.location &&
			(
				this.removeAmpQueryVars( this.removeUrlHash( oppositeWindow.location.href ) ) !==
				this.removeAmpQueryVars( this.removeUrlHash( win.location.href ) )
			)
		) {
			oppositeWindow.location.replace( this.removeAmpQueryVars( win.location.href ) );
			return;
		}

		document.title = '🔄 ' + win.document.title;

		history.replaceState(
			{},
			'',
			this.addPairedBrowsingQueryVar( this.removeAmpQueryVars( win.location.href ) ),
		);
	}
}

window.pairedBrowsingApp = new PairedBrowsingApp();
