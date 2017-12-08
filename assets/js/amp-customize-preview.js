/* exported ampCustomizePreview */

var ampCustomizePreview = ( function( api ) {
	'use strict';

	var self = {};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object} Object data.
	 * @return {void}
	 */
	self.boot = function( data ) {
		if ( ! _.isUndefined( data.ampAvailable ) ) {
			api.bind( 'preview-ready', function() {
				api.preview.bind( 'active', function() {
					api.preview.send( 'amp-status', data.ampAvailable );
				} );
			} );
		}
	};

	return self;

} )( wp.customize );
