/* global ampSlug, ampPairedBrowsingQueryVar */

class PairedBrowsingApp {
	/**
	 * Set the iframes on init.
	 */
	constructor() {
		this.nonAmpIframe = document.getElementById( 'non-amp' );
		this.ampIframe = document.getElementById( 'amp' );

		// If both iframes have loaded successfully, then let's check every second to see if any
		// iframe has diverged from the paired browsing experience.
		Promise.all( this.getIframeLoadedPromises() ).then( () => {
			setInterval( () => this.checkConnectedIframes(), 1000 );
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
			! ( this.nonAmpIframe.contentWindow && this.nonAmpIframe.contentWindow.ampPairedBrowsingClient )
		);

		this.nonAmpIframe.classList.toggle(
			'disconnected',
			! ( this.ampIframe.contentWindow && this.ampIframe.contentWindow.ampPairedBrowsingClient )
		);
	}

	/**
	 * Removes the AMP query variable from the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL without the AMP query variable.
	 */
	removeAmpQueryVar( url ) {
		const modifiedUrl = new URL( url );
		modifiedUrl.searchParams.delete( ampSlug );
		return modifiedUrl.href;
	}

	/**
	 * Adds the AMP query variable to the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL with the AMP query variable.
	 */
	addAmpQueryVar( url ) {
		const modifiedUrl = new URL( url );
		modifiedUrl.searchParams.set( ampSlug, '' );
		return modifiedUrl.href;
	}

	/**
	 * Adds the AMP paired browsing query variable to the supplied URL.
	 *
	 * @param {string} url URL string.
	 * @return {string} Modified URL with the AMP paired browsing query variable.
	 */
	addPairedBrowsingQueryVar( url ) {
		const modifiedUrl = new URL( url );
		modifiedUrl.searchParams.set( ampPairedBrowsingQueryVar, '1' );
		return modifiedUrl.href;
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
	 * Registers the client window with its parent, so that it can be managed by it.
	 *
	 * @param {Window} win Document window.
	 */
	registerClientWindow( win ) {
		let oppositeWindow;

		if ( win === this.ampIframe.contentWindow ) {
			// Force the AMP iframe to always have an AMP URL, if an AMP version is available.
			if ( ! this.documentIsAmp( win.document ) && win.document.querySelector( 'head > link[rel=amphtml]' ) ) {
				win.location.replace( this.addAmpQueryVar( win.location ) );
				return;
			}

			oppositeWindow = this.nonAmpIframe.contentWindow;
		} else {
			// Force the non-AMP iframe to always have a non-AMP URL.
			if ( this.documentIsAmp( win.document ) ) {
				win.location.replace( this.removeAmpQueryVar( win.location ) );
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
			{ passive: true }
		);

		// Make sure the opposite iframe is set to match.
		if (
			oppositeWindow &&
			oppositeWindow.location &&
			(
				this.removeAmpQueryVar( this.removeUrlHash( oppositeWindow.location ) ) !==
				this.removeAmpQueryVar( this.removeUrlHash( win.location ) )
			)
		) {
			oppositeWindow.location.replace( this.removeAmpQueryVar( win.location ) );
			return;
		}

		document.title = '🔄 ' + win.document.title;

		history.replaceState(
			{},
			'',
			this.addPairedBrowsingQueryVar( this.removeAmpQueryVar( win.location ) )
		);
	}
}

window.pairedBrowsingApp = new PairedBrowsingApp();
