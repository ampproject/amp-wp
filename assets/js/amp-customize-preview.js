/* exported ampCustomizePreview */

var ampCustomizePreview = ( function( api ) { // eslint-disable-line no-unused-vars
	'use strict';

	var component = {};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object}  data - PHP exports.
	 * @param {boolean} data.available - Whether AMP is available.
	 * @param {boolean} data.enabled - Whether AMP is enabled.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		api.bind( 'preview-ready', function() {
			api.preview.bind( 'active', function() {
				api.preview.send( 'amp-status', data );
			} );
		} );
	};

	return component;
}( wp.customize ) );
