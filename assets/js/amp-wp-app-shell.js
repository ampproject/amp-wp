/* global ampWpAppShell */
/* eslint-disable no-console */

( window.AMP = window.AMP || [] ).push( ( AMP ) => {
	const currentUrl = new URL( location.href );

	console.info( "Called AMP Shadow callback! AMP.attachShadowDoc:", AMP.attachShadowDoc );

	const fetchDocument = ( url ) => {
		// unfortunately fetch() does not support retrieving documents,
		// so we have to resort to good old XMLHttpRequest.
		const xhr = new XMLHttpRequest();

		// @todo Handle reject.
		return new Promise( ( resolve ) => {
			xhr.open( 'GET', url, true );
			xhr.responseType = 'document';
			xhr.setRequestHeader( 'Accept', 'text/html' );
			xhr.onload = () => {
				resolve( xhr.responseXML );
			};
			xhr.send();
		} );
	};

	if ( parseInt( currentUrl.searchParams.get( 'amp_shadow_doc_populate' ) ) ) {
		currentUrl.searchParams.set( 'amp_app_shell_component', 'inner' );
		const container = document.getElementById( ampWpAppShell.contentElementId );

		fetchDocument( currentUrl ).then( ( doc ) => {
			const shadowDoc = AMP.attachShadowDoc( container, doc, currentUrl );
			console.info( 'Shadow doc:', shadowDoc );
		} );
	}
} );
