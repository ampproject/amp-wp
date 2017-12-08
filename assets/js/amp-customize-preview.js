( function( api, $ ) {
	'use strict';

	api.bind( 'preview-ready', function() {
		var available = ampVars.ampAvailable === 'true';
		api.preview.send( 'amp-status', available );
	} );

} )( wp.customize, jQuery );