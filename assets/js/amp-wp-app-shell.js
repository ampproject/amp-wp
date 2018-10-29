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
		document.body.addEventListener( 'submit', handleSubmit );

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

		event.preventDefault();
		loadUrl( url );
	};

	/**
	 * Handle popstate event.
	 */
	const handlePopState = () => {
		// @todo
	};

	/**
	 * Handle submit on forms.
	 *
	 * @param {Event} event - Event.
	 */
	const handleSubmit = ( event ) => {
		if ( ! event.target.matches( 'form[action]' ) || event.target.method.toUpperCase() !== 'GET' ) {
			return;
		}

		const url = new URL( event.target.action );
		if ( ! isLoadableURL( url ) ) {
			return;
		}

		event.preventDefault();

		for ( const element of event.target.elements ) {
			if ( element.name && ! element.disabled ) {
				// @todo Need to handle radios, checkboxes, submit buttons, etc.
				url.searchParams.set( element.name, element.value );
			}
		}
		loadUrl( url );
	};

	/**
	 * Load URL.
	 *
	 * @todo When should scroll to the top? Only if the first element of the content is not visible?
	 * @param {string|URL} url - URL.
	 */
	const loadUrl = ( url, { scrollIntoView = false } = {} ) => {
		const ampUrl = new URL( url );
		ampUrl.searchParams.set( ampAppShell.ampQueryVar, '1' );
		ampUrl.searchParams.set( ampAppShell.componentQueryVar, 'inner' );

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
				// @todo Synchronize additional meta in head.
				// Update body class name.
				document.body.className = doc.querySelector( 'body' ).className;
				document.title = currentShadowDoc.title;
				history.pushState( {}, currentShadowDoc.title, currentShadowDoc.canonicalUrl );

				currentShadowDoc.ampdoc.whenReady().then( () => {
					newContainer.shadowRoot.addEventListener( 'click', handleClick );
					newContainer.shadowRoot.addEventListener( 'submit', handleSubmit );

					if ( scrollIntoView ) {
						document.body.scrollIntoView( {
							block: 'start',
							inline: 'start',
							behavior: 'smooth'
						} );
					}
				} );
			}
		);
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
