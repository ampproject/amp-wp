/* global URLS */
// See AMP_Service_Workers::add_amp_runtime_caching().
{
	self.addEventListener( 'install', event => {
		event.waitUntil(
			caches.open( wp.serviceWorker.core.cacheNames.runtime ).then( cache => cache.addAll( URLS ) )
		);
	} );
}
