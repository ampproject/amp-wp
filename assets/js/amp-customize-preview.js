/* exported ampCustomizePreview */

var ampCustomizePreview = ( function( api ) {
	'use strict';

	var component = {};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object} data Object data.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		if ( ! _.isUndefined( data.ampAvailable ) ) {
			api.bind( 'preview-ready', function() {
				api.preview.bind( 'active', function() {
					api.preview.send( 'amp-status', data.ampAvailable );
				} );
			} );
		}
	};

	return component;

} )( wp.customize );
