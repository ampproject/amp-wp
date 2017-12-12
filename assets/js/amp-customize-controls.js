/* exported ampCustomizeControls */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, 1 ] } ] */

var ampCustomizeControls = ( function( api, $ ) {
	'use strict';

	var component = {
		data: {
			query: ''
		},
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

		// Defaults.
		api.state.add( 'ampEnabled', new api.Value( false ) );
		api.state.add( 'ampAvailable', new api.Value( false ) );

		api.bind( 'ready', function() {
			api.panel( 'amp_panel', component.panelReady );
		} );
	};

	/**
	 * Check if the URL is AMPified.
	 *
	 * @param {string} url URL.
	 * @return {boolean} whether it is an AMP URL.
	 */
	component.isAmpUrl = function isAmpUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexEndpoint = new RegExp( '\\/' + component.data.query + '\\/?$' );

		urlParser.href = url;
		if ( ! _.isUndefined( wp.customize.utils.parseQueryString( urlParser.search.substr( 1 ) )[ component.data.query ] ) ) {
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
			regexEndpoint = new RegExp( '\\/' + component.data.query + '\\/?$' ),
			params;

		urlParser.href = url;
		urlParser.pathname = urlParser.pathname.replace( regexEndpoint, '' );

		if ( urlParser.search.length > 1 ) {
			params = wp.customize.utils.parseQueryString( urlParser.search.substr( 1 ) );
			delete params[ component.data.query ];
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
		urlParser.search += component.data.query + '=1';
		return urlParser.href;
	};

	/**
	 * Hook up all AMP preview interactions once panel is ready.
	 *
	 * @param {wp.customize.Panel} panel The AMP panel.
	 * @return {void}
	 */
	component.panelReady = function panelReady( panel ) {
		var ampToggleContainer = $( wp.template( 'customize-amp-enabled-toggle' )() ),
			checkbox = ampToggleContainer.find( 'input[type=checkbox]' ),
			tooltip = ampToggleContainer.find( '.tooltip' ),
			tooltipLink = tooltip.find( 'a' ),
			tooltipTimer = 5000,
			tooltipTimeoutId;

		/**
		 * Make current URL AMPified if toggle is on.
		 *
		 * @param {string} url URL.
		 * @return {string} AMPified URL.
		 */
		function setCurrentAmpUrl( url ) {
			var enabled = api.state( 'ampEnabled' ).get();
			if ( ! enabled && component.isAmpUrl( url ) ) {
				return component.unampifyUrl( url );
			} else if ( enabled && ! component.isAmpUrl( url ) ) {
				return component.ampifyUrl( url );
			}
			return url;
		}

		/**
		 * Swap to AMP version of URL in preview.
		 *
		 * @return {void}
		 */
		function updatePreviewUrl() {
			api.previewer.previewUrl.set( setCurrentAmpUrl( api.previewer.previewUrl.get() ) );
		}

		// AMP panel triggers the input toggle for AMP preview.
		panel.expanded.bind( function() {
			if ( api.state( 'ampAvailable' ).get() ) {
				api.state( 'ampEnabled' ).set( panel.expanded.get() );
			}
		} );

		// Enable AMP toggle if available and mobile device selected.
		api.previewedDevice.bind( function( device ) {
			if ( 'mobile' === device && api.state( 'ampAvailable' ).get() ) {
				api.state( 'ampEnabled' ).set( true );
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
					val = setCurrentAmpUrl( val );
				}
				return val;
			};
		} )( api.previewer.previewUrl.validate );

		// Listen for ampEnabled state changes.
		api.state( 'ampEnabled' ).bind( function( enabled ) {
			checkbox.prop( 'checked', enabled );
			updatePreviewUrl();
		} );

		// Listen for ampAvailable state changes.
		api.state( 'ampAvailable' ).bind( function( available ) {
			checkbox.toggleClass( 'disabled', ! available );
			component.tooltipVisible.set( ! available );
		} );

		// Adding checkbox toggle before device selection.
		$( '.devices-wrapper' ).before( ampToggleContainer );

		// User clicked link within tooltip, go to linked post in preview.
		tooltipLink.on( 'click', function( event ) {
			event.preventDefault();
			api.state( 'ampEnabled' ).set( true );
			api.previewer.previewUrl.set( $( this ).prop( 'href' ) );
		} );

		/**
		 * Try closing the tooltip after the timeout.
		 *
		 * @returns {void}
		 */
		function tryToClose() {
			clearTimeout( tooltipTimeoutId );
			tooltipTimeoutId = setTimeout( function() {
				if ( ! component.tooltipVisible.get() ) {
					return;
				}
				if ( component.tooltipFocused.get() > 0 ) {
					tryToClose();
				} else {
					component.tooltipVisible.set( false );
				}
			}, tooltipTimer );
		}

		// Toggle visibility of tooltip based on tooltipVisible state.
		component.tooltipVisible.bind( function( visible ) {
			tooltip.attr( 'aria-hidden', visible ? 'false' : 'true' );
			if ( visible ) {
				$( document ).on( 'click.amp-toggle-outside', function( event ) {
					if ( ! $.contains( ampToggleContainer[0], event.target ) ) {
						component.tooltipVisible.set( false );
					}
				} );
				tooltip.fadeIn();
				tryToClose();
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

} )( wp.customize, jQuery );
