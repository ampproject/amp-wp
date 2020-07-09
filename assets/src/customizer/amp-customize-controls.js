/* global jQuery */

window.ampCustomizeControls = ( function( api, $ ) {
	'use strict';

	const component = {
		data: {
			queryVar: '',
			l10n: {
				ampVersionNotice: '',
			},
		},
	};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object} data Object data.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		component.data = data;

		// Set up Reader theme customizer.
		const previewNotice = $( '#customize-info .preview-notice' );
		previewNotice.text( component.data.l10n.ampVersionNotice );

		$.ajaxPrefilter( component.injectAmpIntoAjaxRequests );

		wp.customize.bind( 'ready', () => {
			/*
			 * Persist the presence or lack of the amp=1 param when navigating in the preview,
			 * even if current page is not yet supported.
			 */
			api.previewer.previewUrl.validate = ( function( prevValidate ) {
				return function( value ) {
					let val = prevValidate.call( this, value );
					if ( val ) {
						const url = new URL( val );
						if ( ! url.searchParams.has( component.data.queryVar ) ) {
							url.searchParams.append( component.data.queryVar, '1' );
							val = url.href;
						}
					}
					return val;
				};
			}( api.previewer.previewUrl.validate ) );
		} );
	};

	/**
	 * Rewrite Ajax requests to inject AMP query var.
	 *
	 * @param {Object} options Options.
	 * @param {string} options.type Type.
	 * @param {string} options.url URL.
	 * @return {void}
	 */
	component.injectAmpIntoAjaxRequests = function injectAmpIntoAjaxRequests( options ) {
		const url = new URL( options.url, window.location.href );
		if ( ! url.searchParams.has( component.data.queryVar ) ) {
			url.searchParams.append( component.data.queryVar, '1' );
		}
		options.url = url.href;
	};

	return component;
}( wp.customize, jQuery ) );
