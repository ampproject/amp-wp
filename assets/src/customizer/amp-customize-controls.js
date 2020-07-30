/* global jQuery */

import { __, _n, sprintf } from '@wordpress/i18n';

window.ampCustomizeControls = ( function( api, $ ) {
	'use strict';

	const component = {
		nonAmpCustomizerLink: null,
		data: {
			queryVar: '',
			l10n: {
				ampVersionNotice: '',
				optionSettingNotice: '',
				navMenusPanelNotice: '',
				rootPanelDescription: '',
			},
			optionSettings: [],
			activeThemeSettingImports: {},
			mimeTypeIcons: {
				image: '',
				document: '',
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

		component.updatePreviewNotice();
		component.extendRootDescription();

		$.ajaxPrefilter( component.injectAmpIntoAjaxRequests );
		api.bind( 'ready', component.updateNonAmpCustomizeLink );
		api.bind( 'ready', component.forceAmpPreviewUrl );
		api.bind( 'ready', component.addOptionSettingNotices );
		api.bind( 'ready', component.addNavMenuPanelNotice );
		api.bind( 'ready', component.addActiveThemeSettingsImporting );
	};

	/**
	 * Update preview notice.
	 */
	component.updatePreviewNotice = function updatePreviewNotice() {
		const previewNotice = $( '#customize-info .preview-notice' );
		previewNotice.html( component.data.l10n.ampVersionNotice ); // Contents have been sanitized with wp_kses_post().
		component.nonAmpCustomizerLink = previewNotice.find( 'a[href]' )[ 0 ];
	};

	/**
	 * Make sure the non-AMP Customizer link keeps referencing to the currently-previewed URL.
	 */
	component.updateNonAmpCustomizeLink = function updateNonAmpCustomizeLink() {
		if ( ! ( component.nonAmpCustomizerLink instanceof HTMLAnchorElement ) ) {
			return;
		}

		const update = () => {
			const previewUrl = new URL( api.previewer.previewUrl() );
			previewUrl.searchParams.delete( component.data.queryVar );

			const customizeUrl = new URL( component.nonAmpCustomizerLink.href );
			customizeUrl.searchParams.set( 'url', previewUrl );
			component.nonAmpCustomizerLink.href = customizeUrl.href;
		};

		update();
		api.previewer.previewUrl.bind( update );
	};

	/**
	 * Add AMP-specific info to the root panel description.
	 */
	component.extendRootDescription = function extendRootDescription() {
		const panelDescription = $( '#customize-info .customize-panel-description' );

		// Ensure the original description is in a paragraph (where normally it is not).
		if ( panelDescription.find( 'p' ).length === 0 ) {
			const originalParagraph = $( '<p></p>' );
			originalParagraph.html( panelDescription.html() );
			panelDescription.html( '' );
			panelDescription.append( originalParagraph );
		}

		const ampDescription = $( '<p>' + component.data.l10n.rootPanelDescription + '</p>' ); // Contents have been sanitized with wp_kses_post().
		panelDescription.append( ampDescription );
	};

	/**
	 * i18n friendly version of basename()
	 *
	 * This is a port of wp_basename() in PHP.
	 *
	 * @param {string} path Path.
	 * @return {string} Basename.
	 */
	function wpBasename( path ) {
		return decodeURIComponent(
			encodeURIComponent( path )
				.replace( /%(2F|5C)/g, '/' )
				.replace( /^.*\//, '' ),
		);
	}

	/**
	 * Ensure UploadControl is updated when underlying setting is programmatically updated (not using media library).
	 *
	 * The newer MediaControl does update programmatically when the setting changes, so the control for the newer
	 * Custom Logo will update their UI to show the image. Older controls like the Background Image will not however.
	 *
	 * @param {wp.customize.UploadControl} control
	 */
	function populateUploadControl( control ) {
		const value = control.setting();
		if ( ! value || ( control.params.attachment && control.params.attachment.url === value ) ) {
			return;
		}
		const url = new URL( value );

		// The following replicates PHP logic in WP_Customize_Media_Control::to_json().
		const type = [ 'jpg', 'png', 'gif', 'bmp' ].includes( url.pathname.substr( -3 ) ) ? 'image' : 'document';
		const attachment = {
			id: 1,
			url: url.href,
			type,
			icon: component.data.mimeTypeIcons[ type ],
			title: wpBasename( url.pathname ),
		};
		if ( 'image' === type ) {
			attachment.sizes = {
				full: {
					url: url.href,
				},
			};
		}

		// Make sure that the media frame is populated with the attachment.
		if ( ! control.frame ) {
			control.initFrame();
		}
		if ( ! control.frame.state() ) {
			control.frame.setState( 'library' );
		}
		control.frame.state().get( 'selection' ).set( [ attachment ] );

		// Call the select method so that the attachment param is updated.
		if ( control.extended( api.BackgroundControl ) ) {
			// Explicitly do not call BackgroundControl#select() because it sends an unnecessary custom-background-add ajax request.
			api.UploadControl.prototype.select.call( control );
		} else {
			control.select();
		}

		// Finally, render the control.
		control.renderContent();
	}

	/**
	 * Ensure HeaderControl is updated when underlying setting is programmatically updated (not using media library).
	 *
	 * The newer MediaControl does update programmatically when the setting changes, so the control for the newer
	 * Custom Logo will update their UI to show the image. Older controls like the Header Image will not however.
	 *
	 * @param {wp.customize.HeaderControl} control
	 */
	function populateHeaderControl( control ) {
		const headerImagedData = api( 'header_image_data' ).get();
		if ( headerImagedData ) {
			control.setImageFromURL(
				headerImagedData.url,
				headerImagedData.attachment_id,
				headerImagedData.width,
				headerImagedData.height,
			);
		}
	}

	/**
	 * Import settings.
	 */
	function importSettings() {
		for ( const [ settingId, settingValue ] of Object.entries( component.data.activeThemeSettingImports ) ) {
			const setting = api( settingId );
			if ( setting ) {
				setting.set( settingValue );
			}
		}

		// Work around the UploadControl not supporting programmatic setting updates.
		api.control.each( ( control ) => {
			const controlImportedSetting = Object.values( control.settings ).find( ( setting ) => {
				return setting.id in component.data.activeThemeSettingImports;
			} );
			if ( ! controlImportedSetting ) {
				return;
			}

			if ( control.extended( api.UploadControl ) ) {
				populateUploadControl( control );
			} else if ( control.extended( api.HeaderControl ) ) {
				populateHeaderControl( control );
			}
		} );
	}

	/**
	 * Add ability to import settings from the active theme.
	 */
	component.addActiveThemeSettingsImporting = function addActiveThemeSettingsImporting() {
		const differingSettings = new Set();
		for ( const [ settingId, settingValue ] of Object.entries( component.data.activeThemeSettingImports ) ) {
			const setting = api( settingId );
			if ( setting && ! _.isEqual( setting(), settingValue ) ) {
				differingSettings.add( settingId );
			}
		}

		// Abort adding any UI if there are no settings to import.
		if ( differingSettings.size === 0 ) {
			return;
		}

		const controlsWithSettings = new Set();
		api.control.each( ( control ) => {
			for ( const setting of Object.values( control.settings ) ) {
				if ( differingSettings.has( setting.id ) ) {
					controlsWithSettings.add( control );
				}
			}
		} );

		const container = $( '<div id="amp-active-theme-settings-importing"></div>' );
		const importButton = $( '<button type="button" class="button button-secondary"></button>' );
		importButton.text(
			__( 'Import Active Theme\'s Settings', 'amp' ) + sprintf( ' (%d)', controlsWithSettings.size ),
		);
		importButton.on( 'click', importSettings );
		container.append( importButton );

		const ol = $( '<ol>' );
		const sortedControls = Array.from( controlsWithSettings );
		sortedControls.sort( ( a, b ) => {
			return a.priority() - b.priority();
		} );

		for ( const sortedControl of sortedControls ) {
			const li = $( '<li></li>' );
			li.text( sortedControl.params.label );
			ol.append( li );
		}
		container.append( ol );

		$( '#customize-info' ).after( container );
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
