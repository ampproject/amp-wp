( () => {
	/*
	 * Use a stale-while-revalidate strategy to cache responses from the AMP CDN and Google Fonts.
	 * This should eventually be implemented using Workbox.
	 */

	const cachedOrigins = new Set( [
		'https://fonts.googleapis.com',
		'https://cdn.ampproject.org',
		'https://fonts.gstatic.com'
	] );

	self.addEventListener( 'fetch', ( event ) => {
		if ( 'GET' !== event.request.method ) {
			return;
		}
		const url = new URL( event.request.url );
		if ( ! cachedOrigins.has( url.origin ) ) {
			return;
		}

		// Props jakearchibald: https://developers.google.com/web/fundamentals/instant-and-offline/offline-cookbook/#stale-while-revalidate
		event.respondWith(
			caches.open( 'amp' ).then( ( cache ) => {
				return cache.match( event.request ).then( ( response ) => {
					var fetchPromise = fetch( event.request ).then( ( networkResponse ) => {
						cache.put( event.request, networkResponse.clone() );
						return networkResponse;
					} );
					return response || fetchPromise;
				} );
			} )
		);
	} );
} )();
