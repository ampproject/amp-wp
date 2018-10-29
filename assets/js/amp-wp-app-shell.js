/* global ampAppShell, AMP */
/* eslint-disable no-console */

{
	let currentShadowDoc;

	/**
	 * Initialize.
	 */
	const init = () => {
		const container = document.getElementById( ampAppShell.contentElementId );
		if ( ! container ) {
			throw new Error( 'Lacking element with ID: ' + ampAppShell.contentElementId );
		}

		// @todo Intercept GET submissions.
		// @todo Make sure that POST submissions are handled.
		document.body.addEventListener( 'click', handleClick );

		window.addEventListener( 'popstate', handlePopState );
	};

	/**
	 * Is loadable URL.
	 *
	 * @param {URL} url - URL to be loaded.
	 * @return {boolean} Whether the URL can be loaded into a shadow doc.
	 */
	const isLoadableURL = ( url ) => {
		if ( url.pathname.endsWith( '.php' ) ) {
			return false;
		}
		if ( url.href.startsWith( ampAppShell.adminUrl ) ) {
			return false;
		}
		return url.href.startsWith( ampAppShell.homeUrl );
	};

	/**
	 * Handle clicks on links.
	 *
	 * @param {MouseEvent} event - Event.
	 */
	const handleClick = ( event ) => {
		if ( ! event.target.matches( 'a[href]' ) ) {
			return;
		}

		// @todo Handle page anchor links.
		const url = new URL( event.target.href );
		if ( ! isLoadableURL( url ) ) {
			return;
		}

		const ampUrl = new URL( url );
		ampUrl.searchParams.set( ampAppShell.ampQueryVar, '1' );
		ampUrl.searchParams.set( ampAppShell.componentQueryVar, 'inner' );

		event.preventDefault();
		fetchDocument( ampUrl ).then(
			( doc ) => {
				if ( currentShadowDoc ) {
					currentShadowDoc.close();
				}

				const oldContainer = document.getElementById( ampAppShell.contentElementId );
				const newContainer = document.createElement( oldContainer.nodeName );
				newContainer.setAttribute( 'id', oldContainer.getAttribute( 'id' ) );
				oldContainer.parentNode.replaceChild( newContainer, oldContainer );

				currentShadowDoc = AMP.attachShadowDoc( newContainer, doc, url.toString() );

				// @todo Update nav menus.
				// @todo Improve styling of header when transitioning between home and non-home.
				// Update body class name.
				document.body.className = doc.querySelector( 'body' ).className;
				document.title = currentShadowDoc.title;
				history.pushState( {}, currentShadowDoc.title, currentShadowDoc.canonicalUrl );

				currentShadowDoc.ampdoc.whenReady().then( () => {
					newContainer.shadowRoot.addEventListener( 'click', handleClick );
				} );
			}
		);
	};

	/**
	 * Handle popstate event.
	 */
	const handlePopState = () => {
		// @todo
	};

	/**
	 * Fetch document.
	 *
	 * @param {string|URL} url URL.
	 * @return {Promise<Document>} Promise which resolves to the fetched document.
	 */
	const fetchDocument = ( url ) => {
		// unfortunately fetch() does not support retrieving documents,
		// so we have to resort to good old XMLHttpRequest.
		const xhr = new XMLHttpRequest();

		// @todo Handle reject.
		return new Promise( ( resolve ) => {
			xhr.open( 'GET', url.toString(), true );
			xhr.responseType = 'document';
			xhr.setRequestHeader( 'Accept', 'text/html' );
			xhr.onload = () => {
				resolve( xhr.responseXML );
			};
			xhr.send();
		} );
	};

	// Initialize when Shadow API loaded and DOM Ready.
	( window.AMP = window.AMP || [] ).push( () => {
		// Code from @wordpress/dom-ready NPM package <https://github.com/WordPress/gutenberg/tree/master/packages/dom-ready>.
		if (
			document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
			document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
		) {
			init();
		} else {
			// DOMContentLoaded has not fired yet, delay callback until then.
			document.addEventListener( 'DOMContentLoaded', init );
		}
	} );
}
