/* global jQuery */

window.ampCustomizeControls = ( function( api, $ ) {
	'use strict';

	const component = {
		data: {
			queryVar: '',
			l10n: {
				ampVersionNotice: '',
				optionSettingNotice: '',
				navMenusPanelNotice: '',
			},
			optionSettings: [],
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

		component.updatePreviewNotice();

		$.ajaxPrefilter( component.injectAmpIntoAjaxRequests );
		api.bind( 'ready', component.forceAmpPreviewUrl );
		api.bind( 'ready', component.addOptionSettingNotices );
		api.bind( 'ready', component.addNavMenuPanelNotice );
	};

	/**
	 * Update preview notice.
	 */
	component.updatePreviewNotice = function updatePreviewNotice() {
		const previewNotice = $( '#customize-info .preview-notice' );
		previewNotice.text( component.data.l10n.ampVersionNotice );
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
			options.url = url.href;
		}
	};

	/**
	 * Persist the presence the amp=1 param when navigating in the preview, even if current page is not yet supported.
	 */
	component.forceAmpPreviewUrl = function forceAmpPreviewUrl() {
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
	};

	/**
	 * Add notice to all settings for options.
	 */
	component.addOptionSettingNotices = function addOptionSettingNotices() {
		for ( const settingId of component.data.optionSettings ) {
			api( settingId, ( setting ) => {
				const notification = new api.Notification(
					'amp_option_setting',
					{
						type: 'info',
						message: component.data.l10n.optionSettingNotice,
					},
				);
				setting.notifications.add( notification.code, notification );
			} );
		}
	};

	/**
	 * Add notice to the nav menus panel.
	 */
	component.addNavMenuPanelNotice = function addNavMenuPanelNotice() {
		api.panel( 'nav_menus', ( panel ) => {
			// Fix bug in WP where the Nav Menus panel lacks a notifications container.
			if ( ! panel.notifications.container.length ) {
				panel.notifications.container = $( '<div class="customize-control-notifications-container"></div>' );
				panel.container.find( '.panel-meta:first' ).append( panel.notifications.container );
			}

			const notification = new api.Notification(
				'amp_version',
				{
					type: 'info',
					message: component.data.l10n.navMenusPanelNotice,
				},
			);
			panel.notifications.add( notification.code, notification );
		} );
	};

	return component;
}( wp.customize, jQuery ) );
