/* global self, caches, URLS */
// See AMP_Service_Workers::add_amp_runtime_caching() and <https://github.com/ampproject/amp-by-example/blob/a4d798cac6a534e0c46e78944a2718a8dab3c057/boilerplate-generator/templates/files/serviceworkerJs.js#L9-L22>.
{
	self.addEventListener( 'install', ( event ) => {
		event.waitUntil(
			caches.open( wp.serviceWorker.core.cacheNames.runtime ).then( ( cache ) => cache.addAll( URLS ) ),
		);
	} );
}
