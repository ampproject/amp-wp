/* exported ampCustomizeControls */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, 1, 250] } ] */

var ampCustomizeControls = ( function( api, $ ) { // eslint-disable-line no-unused-vars
	'use strict';

	var component = {
		data: {
			queryVar: 'amp',
			panelId: '',
			ampUrl: '',
			l10n: {
				unavailableMessage: '',
				unavailableLinkText: ''
			}
		},
		tooltipTimeout: 5000,
		tooltipVisible: new api.Value( false ),
		tooltipFocused: new api.Value( 0 )
	};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object} data Object data.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		component.data = data;

		function initPanel() {
			api.panel( component.data.panelId, component.panelReady );
		}

		if ( api.state ) {
			component.addState();
			api.bind( 'ready', initPanel );
		} else { // WP<4.9.
			api.bind( 'ready', function() {
				component.addState(); // Needed for WP<4.9.
				initPanel();
			} );
		}
	};

	/**
	 * Add state for AMP.
	 *
	 * @return {void}
	 */
	component.addState = function addState() {
		api.state.add( 'ampEnabled', new api.Value( false ) );
		api.state.add( 'ampAvailable', new api.Value( false ) );
	};

	/**
	 * Check if the URL is AMPified.
	 *
	 * @param {string} url URL.
	 * @return {boolean} whether it is an AMP URL.
	 */
	component.isAmpUrl = function isAmpUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexEndpoint = new RegExp( '\\/' + component.data.queryVar + '\\/?$' );

		urlParser.href = url;
		if ( ! _.isUndefined( wp.customize.utils.parseQueryString( urlParser.search.substr( 1 ) )[ component.data.queryVar ] ) ) {
			return true;
		}
		return regexEndpoint.test( urlParser.pathname );
	};

	/**
	 * Create an non-AMP version of a URL.
	 *
	 * @param {string} url URL.
	 * @return {string} non-AMPified URL.
	 */
	component.unampifyUrl = function unampifyUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexEndpoint = new RegExp( '\\/' + component.data.queryVar + '\\/?$' ),
			params;

		urlParser.href = url;
		urlParser.pathname = urlParser.pathname.replace( regexEndpoint, '' );

		if ( 1 < urlParser.search.length ) {
			params = wp.customize.utils.parseQueryString( urlParser.search.substr( 1 ) );
			delete params[ component.data.queryVar ];
			urlParser.search = $.param( params );
		}

		return urlParser.href;
	};

	/**
	 * Create an AMP version of a URL.
	 *
	 * @param {string} url URL.
	 * @return {string} AMPified URL.
	 */
	component.ampifyUrl = function ampifyUrl( url ) {
		var urlParser = document.createElement( 'a' );
		urlParser.href = component.unampifyUrl( url );
		if ( urlParser.search.length ) {
			urlParser.search += '&';
		}
		urlParser.search += component.data.queryVar + '=1';
		return urlParser.href;
	};

	/**
	 * Try to close the tooltip after a given timeout.
	 *
	 * @return {void}
	 */
	component.tryToCloseTooltip = function tryToCloseTooltip() {
		clearTimeout( component.tooltipTimeoutId );
		component.tooltipTimeoutId = setTimeout( function() {
			if ( ! component.tooltipVisible.get() ) {
				return;
			}
			if ( 0 < component.tooltipFocused.get() ) {
				component.tryToCloseTooltip();
			} else {
				component.tooltipVisible.set( false );
			}
		}, component.tooltipTimeout );
	};

	/**
	 * Make current URL AMPified if toggle is on.
	 *
	 * @param {string} url URL.
	 * @return {string} AMPified URL.
	 */
	component.setCurrentAmpUrl = function setCurrentAmpUrl( url ) {
		var enabled = api.state( 'ampEnabled' ).get();
		if ( ! enabled && component.isAmpUrl( url ) ) {
			return component.unampifyUrl( url );
		} else if ( enabled && ! component.isAmpUrl( url ) ) {
			return component.ampifyUrl( url );
		}
		return url;
	};

	/**
	 * Swap to AMP version of URL in preview.
	 *
	 * @return {void}
	 */
	component.updatePreviewUrl = function updatePreviewUrl() {
		api.previewer.previewUrl.set( component.setCurrentAmpUrl( api.previewer.previewUrl.get() ) );
	};

	/**
	 * Enable AMP and navigate to the given URL.
	 *
	 * @param {string} url - URL.
	 * @return {void}
	 */
	component.enableAndNavigateToUrl = function enableAndNavigateToUrl( url ) {
		api.state( 'ampEnabled' ).set( true );
		api.previewer.previewUrl.set( url );
	};

	/**
	 * Update panel notifications.
	 *
	 * @return {void}
	 */
	component.updatePanelNotifications = function updatePanelNotifications() {
		var panel = api.panel( component.data.panelId ),
			containers;
		containers = panel.sections().concat( [ panel ] );
		if ( api.state( 'ampAvailable' ).get() ) {
			_.each( containers, function( container ) {
				container.notifications.remove( 'amp_unavailable' );
			} );
		} else {
			_.each( containers, function( container ) {
				container.notifications.add( new api.Notification( 'amp_unavailable', {
					message: component.data.l10n.unavailableMessage,
					type: 'info',
					linkText: component.data.l10n.unavailableLinkText,
					url: component.data.ampUrl,
					templateId: 'customize-amp-unavailable-notification',
					render: function() {
						var li = api.Notification.prototype.render.call( this );
						li.find( 'a' ).on( 'click', function( event ) {
							event.preventDefault();
							component.enableAndNavigateToUrl( this.href );
						} );
						return li;
					}
				} ) );
			} );
		}
	};

	/**
	 * Hook up all AMP preview interactions once panel is ready.
	 *
	 * @param {wp.customize.Panel} panel The AMP panel.
	 * @return {void}
	 */
	component.panelReady = function panelReady( panel ) {
		var ampToggleContainer, checkbox, tooltip, tooltipLink;

		ampToggleContainer = $( wp.template( 'customize-amp-enabled-toggle' )( {
			message: component.data.l10n.unavailableMessage,
			linkText: component.data.l10n.unavailableLinkText,
			url: component.data.ampUrl
		} ) );
		checkbox = ampToggleContainer.find( 'input[type=checkbox]' );
		tooltip = ampToggleContainer.find( '.tooltip' );
		tooltipLink = tooltip.find( 'a' );

		// AMP panel triggers the input toggle for AMP preview.
		panel.expanded.bind( function( expanded ) {
			if ( ! expanded ) {
				return;
			}
			if ( api.state( 'ampAvailable' ).get() ) {
				api.state( 'ampEnabled' ).set( panel.expanded.get() );
			} else if ( ! panel.notifications ) {
				/*
				 * This is only done if panel notifications aren't supported.
				 * If they are (as of 4.9) then a notification will be shown
				 * in the panel and its sections when AMP is not available.
				 */
				setTimeout( function() {
					component.tooltipVisible.set( true );
				}, 250 );
			}
		} );

		if ( panel.notifications ) {
			api.state( 'ampAvailable' ).bind( component.updatePanelNotifications );
			component.updatePanelNotifications();
			api.section.bind( 'add', component.updatePanelNotifications );
		}

		// Enable AMP toggle if available and mobile device selected.
		api.previewedDevice.bind( function( device ) {
			if ( api.state( 'ampAvailable' ).get() ) {
				api.state( 'ampEnabled' ).set( 'mobile' === device );
			}
		} );

		// Message coming from previewer.
		api.previewer.bind( 'amp-status', function( data ) {
			api.state( 'ampAvailable' ).set( data.available );
		} );
		function setInitialAmpEnabledState( data ) {
			api.state( 'ampEnabled' ).set( data.enabled );
			api.previewer.unbind( 'amp-status', setInitialAmpEnabledState );
		}
		api.previewer.bind( 'amp-status', setInitialAmpEnabledState );

		/*
		 * Persist the presence or lack of the amp=1 param when navigating in the preview,
		 * even if current page is not yet supported.
		 */
		api.previewer.previewUrl.validate = ( function( prevValidate ) {
			return function( value ) {
				var val = prevValidate.call( this, value );
				if ( val ) {
					val = component.setCurrentAmpUrl( val );
				}
				return val;
			};
		}( api.previewer.previewUrl.validate ) );

		// Listen for ampEnabled state changes.
		api.state( 'ampEnabled' ).bind( function( enabled ) {
			checkbox.prop( 'checked', enabled );
			component.updatePreviewUrl();
		} );

		// Listen for ampAvailable state changes.
		api.state( 'ampAvailable' ).bind( function( available ) {
			checkbox.toggleClass( 'disabled', ! available );

			// Show the unavailable tooltip if AMP is enabled.
			if ( api.state( 'ampEnabled' ).get() ) {
				component.tooltipVisible.set( ! available );
			}
		} );

		// Adding checkbox toggle before device selection.
		$( '.devices-wrapper' ).before( ampToggleContainer );

		// User clicked link within tooltip, go to linked post in preview.
		tooltipLink.on( 'click', function( event ) {
			event.preventDefault();
			component.enableAndNavigateToUrl( this.href );
		} );

		// Toggle visibility of tooltip based on tooltipVisible state.
		component.tooltipVisible.bind( function( visible ) {
			tooltip.attr( 'aria-hidden', visible ? 'false' : 'true' );
			if ( visible ) {
				$( document ).on( 'click.amp-toggle-outside', function( event ) {
					if ( ! $.contains( ampToggleContainer[ 0 ], event.target ) ) {
						component.tooltipVisible.set( false );
					}
				} );
				tooltip.fadeIn();
				component.tryToCloseTooltip();
			} else {
				tooltip.fadeOut();
				component.tooltipFocused.set( 0 );
				$( document ).off( 'click.amp-toggle-outside' );
			}
		} );

		// Handle click on checkbox to either enable the AMP preview or show the tooltip.
		checkbox.on( 'click', function() {
			this.checked = ! this.checked; // Undo what we just did, since state is managed in ampAvailable change handler.
			if ( api.state( 'ampAvailable' ).get() ) {
				api.state( 'ampEnabled' ).set( ! api.state( 'ampEnabled' ).get() );
			} else {
				component.tooltipVisible.set( true );
			}
		} );

		// Keep track of the user's state interacting with the tooltip.
		tooltip.on( 'mouseenter', function() {
			if ( ! api.state( 'ampAvailable' ).get() ) {
				component.tooltipVisible.set( true );
			}
			component.tooltipFocused.set( component.tooltipFocused.get() + 1 );
		} );
		tooltip.on( 'mouseleave', function() {
			component.tooltipFocused.set( component.tooltipFocused.get() - 1 );
		} );
		tooltipLink.on( 'focus', function() {
			if ( ! api.state( 'ampAvailable' ).get() ) {
				component.tooltipVisible.set( true );
			}
			component.tooltipFocused.set( component.tooltipFocused.get() + 1 );
		} );
		tooltipLink.on( 'blur', function() {
			component.tooltipFocused.set( component.tooltipFocused.get() - 1 );
		} );
	};

	return component;
}( wp.customize, jQuery ) );
