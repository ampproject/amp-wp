/* global Promise */
( () => {
	/*
	 * Use a stale-while-revalidate strategy to cache responses from the AMP CDN.
	 * This should eventually be implemented using Workbox.
	 */
	const CACHE = 'amp';

	self.addEventListener( 'fetch', ( event ) => {
		if ( 'GET' !== event.request.method ) {
			return;
		}
		const url = new URL( event.request.url );
		if ( 'https://cdn.ampproject.org' !== url.origin ) {
			return;
		}
		event.respondWith( fromCache( event.request ) );
		event.waitUntil( update( event.request ) );
	} );

	function fromCache( request ) {
		return caches.open( CACHE ).then( ( cache ) => {
			return cache.match( request ).then( ( matching ) => {
				return matching || Promise.reject( 'no-match' );
			} );
		} );
	}

	function update( request ) {
		return caches.open( CACHE ).then( ( cache ) => {
			return fetch( request ).then( ( response ) => {
				return cache.put( request, response );
			} );
		} );
	}
} )();
