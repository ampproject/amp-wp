/* global ampAppShell, AMP */
/* eslint-disable no-console */

{
	let currentShadowDoc;

	/**
	 * Initialize.
	 */
	function init() {
		const container = document.getElementById( ampAppShell.contentElementId );
		if ( ! container ) {
			throw new Error( 'Lacking element with ID: ' + ampAppShell.contentElementId );
		}

		// @todo Intercept GET submissions.
		// @todo Make sure that POST submissions are handled.
		document.body.addEventListener( 'click', handleClick );
		document.body.addEventListener( 'submit', handleSubmit );

		window.addEventListener( 'popstate', handlePopState );
	}

	/**
	 * Is loadable URL.
	 *
	 * @param {URL} url - URL to be loaded.
	 * @return {boolean} Whether the URL can be loaded into a shadow doc.
	 */
	function isLoadableURL( url ) {
		if ( url.pathname.endsWith( '.php' ) ) {
			return false;
		}
		if ( url.href.startsWith( ampAppShell.adminUrl ) ) {
			return false;
		}
		return url.href.startsWith( ampAppShell.homeUrl );
	}

	/**
	 * Handle clicks on links.
	 *
	 * @param {MouseEvent} event - Event.
	 */
	function handleClick( event ) {
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
	}

	/**
	 * Handle popstate event.
	 */
	function handlePopState() {
		// @todo
	}

	/**
	 * Handle submit on forms.
	 *
	 * @param {Event} event - Event.
	 */
	function handleSubmit( event ) {
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
	}

	/**
	 * Load URL.
	 *
	 * @todo When should scroll to the top? Only if the first element of the content is not visible?
	 * @param {string|URL} url - URL.
	 * @param {boolean} scrollIntoView - Scroll into view.
	 */
	function loadUrl( url, { scrollIntoView = false } = {} ) {
		updateNavMenuClasses( url );

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

				// @todo Improve styling of header when transitioning between home and non-home.
				// @todo Synchronize additional meta in head.
				// Update body class name.
				document.body.className = doc.querySelector( 'body' ).className;
				document.title = currentShadowDoc.title;
				history.pushState( {}, currentShadowDoc.title, currentShadowDoc.canonicalUrl );

				// Update the nav menu classes if the final URL has redirected somewhere else.
				if ( currentShadowDoc.canonicalUrl !== url.toString() ) {
					updateNavMenuClasses( currentShadowDoc.canonicalUrl );
				}

				currentShadowDoc.ampdoc.whenReady().then( () => {
					newContainer.shadowRoot.addEventListener( 'click', handleClick );
					newContainer.shadowRoot.addEventListener( 'submit', handleSubmit );

					/*
					 * Let admin bar in shadow doc replace admin bar in app shell (if it still exists).
					 * Very conveniently the admin bar _inside_ the shadow root can appear _outside_
					 * the shadow root via fixed positioning!
					 */
					const originalAdminBar = document.getElementById( 'wpadminbar' );
					if ( originalAdminBar ) {
						originalAdminBar.remove();
					}

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
	}

	/**
	 * Update class names in nav menus based on what URL is being navigated to.
	 *
	 * Note that this will only be able to account for:
	 *  - current-menu-item (current_{object}_item)
	 *  - current-menu-parent (current_{object}_parent)
	 *  - current-menu-ancestor (current_{object}_ancestor)
	 *
	 * @param {string|URL} url URL.
	 */
	function updateNavMenuClasses( url ) {
		const queriedUrl = new URL( url );
		queriedUrl.hash = '';

		// Remove all contextual class names.
		for ( const relation of [ 'item', 'parent', 'ancestor' ] ) {
			const pattern = new RegExp( '^current[_-](.+)[_-]' + relation + '$' );
			for ( const item of document.querySelectorAll( '.menu-item.current-menu-' + relation + ', .page_item.current_page_' + relation ) ) { // Non-live NodeList.
				for ( const className of Array.from( item.classList ) ) { // Live DOMTokenList.
					if ( pattern.test( className ) ) {
						item.classList.remove( className );
					}
				}
			}
		}

		// Re-add class names to items generated from nav menus.
		for ( const link of document.querySelectorAll( '.menu-item > a[href]' ) ) {
			if ( link.href !== url.href ) {
				continue;
			}

			let menuItemObjectName;
			const menuItemObjectNamePrefix = 'menu-item-object-';
			for ( const className of link.parentElement.classList ) {
				if ( className.startsWith( menuItemObjectNamePrefix ) ) {
					menuItemObjectName = className.substr( menuItemObjectNamePrefix.length );
					break;
				}
			}

			let depth = 0;
			let item = link.parentElement;
			while ( item ) {
				if ( 0 === depth ) {
					item.classList.add( 'current-menu-item' );
					if ( menuItemObjectName ) {
						link.parentElement.classList.add( `current_${menuItemObjectName}_item` );
					}
				} else if ( 1 === depth ) {
					item.classList.add( 'current-menu-parent' );
					item.classList.add( 'current-menu-ancestor' );
					if ( menuItemObjectName ) {
						link.parentElement.classList.add( `current_${menuItemObjectName}_parent` );
						link.parentElement.classList.add( `current_${menuItemObjectName}_ancestor` );
					}
				} else {
					item.classList.add( 'current-menu-ancestor' );
					if ( menuItemObjectName ) {
						link.parentElement.classList.add( `current_${menuItemObjectName}_ancestor` );
					}
				}
				depth++;

				if ( ! item.parentElement ) {
					break;
				}
				item = item.parentElement.closest( '.menu-item-has-children' );
			}

			link.parentElement.classList.add( 'current-menu-item' );
		}

		// Re-add class names to items generated from page listings.
		for ( const link of document.querySelectorAll( '.page_item > a[href]' ) ) {
			if ( link.href !== url.href ) {
				continue;
			}
			let depth = 0;
			let item = link.parentElement;
			while ( item ) {
				if ( 0 === depth ) {
					item.classList.add( 'current_page_item' );
				} else if ( 1 === depth ) {
					item.classList.add( 'current_page_parent' );
					item.classList.add( 'current_page_ancestor' );
				} else {
					item.classList.add( 'current_page_ancestor' );
				}
				depth++;

				if ( ! item.parentElement ) {
					break;
				}
				item = item.parentElement.closest( '.page_item_has_children' );
			}
		}
	}

	/**
	 * Fetch document.
	 *
	 * @param {string|URL} url URL.
	 * @return {Promise<Document>} Promise which resolves to the fetched document.
	 */
	function fetchDocument( url ) {
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
	}

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
