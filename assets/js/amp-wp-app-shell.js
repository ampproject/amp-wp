/* global ampAppShell, AMP */
/* eslint-disable no-console */

( function() {
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

		if ( ampAppShell.isOuterAppShell ) {
			loadUrl( location.href );
		}
	}

	/**
	 * Handle popstate event.
	 */
	function handlePopState() {
		loadUrl( window.location.href, { pushState: false } );
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
		if ( ! event.target.matches( 'a[href]' ) || event.target.closest( '#wpadminbar' ) ) {
			return;
		}

		// Skip handling click if it was handled already.
		if ( event.defaultPrevented ) {
			return;
		}

		// @todo Handle page anchor links.
		const url = new URL( event.target.href );
		if ( ! isLoadableURL( url ) ) {
			return;
		}

		loadUrl( url, { scrollIntoView: true } );
		event.preventDefault();
	}

	/**
	 * Handle submit on forms.
	 *
	 * @todo Handle POST requests.
	 *
	 * @param {Event} event - Event.
	 */
	function handleSubmit( event ) {
		if ( ! event.target.matches( 'form[action]' ) || event.target.method.toUpperCase() !== 'GET' || event.target.closest( '#wpadminbar' ) ) {
			return;
		}

		// Skip handling click if it was handled already.
		if ( event.defaultPrevented ) {
			return;
		}

		const url = new URL( event.target.action );
		if ( ! isLoadableURL( url ) ) {
			return;
		}

		for ( const element of event.target.elements ) {
			if ( element.name && ! element.disabled ) {
				// @todo Need to handle radios, checkboxes, submit buttons, etc.
				url.searchParams.set( element.name, element.value );
			}
		}
		loadUrl( url, { scrollIntoView: true } );
		event.preventDefault();
	}

	/**
	 * Determine whether header is visible at all.
	 *
	 * @return {boolean} Whether header image is visible.
	 */
	function isHeaderVisible() {
		const element = document.querySelector( '.site-branding' );
		if ( ! element ) { 
			return;
		} 
		const clientRect = element.getBoundingClientRect();
		return clientRect.height + clientRect.top >= 0;
	}

	/**
	 * Fetch response for URL of shadow doc.
	 *
	 * @param {string} url - URL.
	 * @return {Promise<Response>} Response promise.
	 */
	function fetchShadowDocResponse( url ) {
		const ampUrl = new URL( url );
		const pathSuffix = '_' + ampAppShell.componentQueryVar + '_inner';
		ampUrl.pathname = ampUrl.pathname + pathSuffix;

		return fetch( ampUrl.toString(), {
			method: 'GET',
			mode: 'same-origin',
			credentials: 'include',
			redirect: 'follow',
			cache: 'default',
			referrer: 'client'
		} );
	}

	/**
	 * Load URL.
	 *
	 * @todo When should scroll to the top? Only if the first element of the content is not visible?
	 * @param {string|URL} url - URL.
	 * @param {boolean} scrollIntoView - Scroll into view.
	 * @param {boolean} pushState - Whether to push state.
	 */
	function loadUrl( url, { scrollIntoView = false, pushState = true } = {} ) {
		const previousUrl = location.href;
		const navigateEvent = new CustomEvent( 'wp-amp-app-shell-navigate', {
			cancelable: true,
			detail: {
				previousUrl,
				url: String( url )
			}
		} );
		if ( ! window.dispatchEvent( navigateEvent ) ) {
			return; // Allow scripts on page to cancel navigation via preventDefault() on wp-amp-app-shell-navigate event.
		}

		updateNavMenuClasses( url );

		fetchShadowDocResponse( url )
			.then( response => {
				if ( currentShadowDoc ) {
					currentShadowDoc.close();
				}

				const oldContainer = document.getElementById( ampAppShell.contentElementId );
				const newContainer = document.createElement( oldContainer.nodeName );
				newContainer.setAttribute( 'id', oldContainer.getAttribute( 'id' ) );
				oldContainer.parentNode.replaceChild( newContainer, oldContainer );

				/*
				 * For more on this, see:
				 * https://www.ampproject.org/latest/blog/streaming-in-the-shadow-reader/
				 * https://github.com/ampproject/amphtml/blob/master/spec/amp-shadow-doc.md#fetching-and-attaching-shadow-docs
				 */
				currentShadowDoc = AMP.attachShadowDocAsStream( newContainer, url, {} );

				// @todo If it is not an AMP document, then the loaded document needs to break out of the app shell. This should be done in readChunk() below.
				currentShadowDoc.ampdoc.whenReady().then( () => {
					let currentUrl;
					if ( currentShadowDoc.canonicalUrl ) {
						currentUrl = new URL( currentShadowDoc.canonicalUrl );

						// Prevent updating the URL if the canonical URL is for the error template.
						// @todo The rel=canonical link should not be output for these templates.
						if ( currentUrl.searchParams.has( 'wp_error_template' ) ) {
							currentUrl = currentUrl.href = url;
						}
					} else {
						currentUrl = new URL( url );
					}

					// Update the nav menu classes if the final URL has redirected somewhere else.
					if ( currentUrl.toString() !== url.toString() ) {
						updateNavMenuClasses( currentUrl );
					}

					// @todo Improve styling of header when transitioning between home and non-home.
					// @todo Synchronize additional meta in head.
					// Update body class name.
					document.body.className = newContainer.shadowRoot.querySelector( 'body' ).className;
					document.title = currentShadowDoc.title;
					if ( pushState ) {
						history.pushState(
							{},
							currentShadowDoc.title,
							currentUrl.toString()
						);
					}

					// @todo Consider allowing cancelable and when happens to prevent default initialization.
					const readyEvent = new CustomEvent( 'wp-amp-app-shell-ready', {
						cancelable: false,
						detail: {
							previousUrl,
							oldContainer,
							newContainer,
							shadowDoc: currentShadowDoc
						}
					} );
					window.dispatchEvent( readyEvent );

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

					if ( scrollIntoView && ! isHeaderVisible() ) {
						// @todo The scroll position is not correct when admin bar is used. Consider scrolling to Y coordinate smoothly instead.
						document.querySelector( '.site-content-contain' ).scrollIntoView( {
							block: 'start',
							inline: 'start',
							behavior: 'smooth'
						} );
					}
				} );

				const reader = response.body.getReader();
				const decoder = new TextDecoder();

				function readChunk() {
					return reader.read().then( chunk => {
						const input = chunk.value || new Uint8Array();
						const text = decoder.decode(
							input,
							{
								stream: ! chunk.done
							}
						);
						if ( text ) {
							currentShadowDoc.writer.write( text );
						}
						if ( chunk.done ) {
							currentShadowDoc.writer.close();
						} else {
							return readChunk();
						}
					} );
				}

				return readChunk();
			} )
			.catch( ( error ) => {
				if ( 'amp_unavailable' === error ) {
					window.location.assign( url );
				} else {
					console.error( error );
				}
			} );
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
			if ( link.href !== queriedUrl.href ) {
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
			if ( link.href !== queriedUrl.href ) {
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

	// Initialize when Shadow DOM API loaded and DOM Ready.
	const ampReadyPromise = new Promise( resolve => {
		if ( ! window.AMP ) {
			window.AMP = [];
		}
		window.AMP.push( resolve );
	} );
	const shadowDomPolyfillReadyPromise = new Promise( resolve => {
		if ( Element.prototype.attachShadow ) {
			// Native available.
			resolve();
		} else {
			// Otherwise, wait for polyfill to be installed.
			window.addEventListener( 'WebComponentsReady', resolve );
		}
	} );
	Promise.all( [ ampReadyPromise, shadowDomPolyfillReadyPromise ] ).then( () => {
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
} )();
