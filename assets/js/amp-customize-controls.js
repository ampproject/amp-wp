/* exported ampCustomizeControls */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1 ] } ] */

var ampCustomizeControls = ( function( api, $ ) {
	'use strict';

	var component = {
		data: {
			defaultPost: '',
			query: '',
			strings: {
				compat: '',
				navigate: ''
			}
		}
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
		api.state.add( 'ampEnabled', new api.Value() ).set( false );
		api.state.add( 'ampAvailable', new api.Value() ).set( true );

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
		/**
		 * Make current URL AMPified if toggle is on.
		 *
		 * @param {string} url URL.
		 * @return {string} AMPified URL.
		 */
		function setCurrentAmpUrl( url ) {
			var ampToggle = api.state( 'ampEnabled' ).get() && api.state( 'ampAvailable' ).get();
			if ( ! ampToggle && component.isAmpUrl( url ) ) {
				return component.unampifyUrl( url );
			} else if ( ampToggle && ! component.isAmpUrl( url ) ) {
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
		api.previewer.bind( 'amp-status', function( available ) {
			api.state( 'ampAvailable' ).set( available );
		} );

		// Persist the presence or lack of the amp=1 param when navigating in the preview,
		// even if current page is not yet supported.
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
			$( '.amp-toggle input' ).prop( 'checked', enabled );
			updatePreviewUrl();
		} );

		// Listen for ampAvailable state changes.
		api.state( 'ampAvailable' ).bind( function( available ) {
			$( '.amp-toggle input' ).prop( 'disabled', ! available );
		} );

		// Adding checkbox toggle before device selection.
		$( '.devices-wrapper' ).before( wp.template( 'amp-customizer-elements' )( {
			compat: component.data.strings.compat,
			url: component.data.defaultPost,
			navigate: component.data.strings.navigate
		} ) );

		// User clicked link within tooltip, go to linked post in preview.
		$( '.amp-toggle .tooltip a' ).on( 'click', function() {
			var url = $( this ).data( 'post' );
			if ( url.length ) {
				api.previewer.previewUrl.set( url );
			}
		} );

		// Main controls for toggling AMP preview.
		$( '#customize-footer-actions' ).on( 'click', '.amp-toggle', function() {
			var $input = $( 'input', $( this ) ),
				$tooltip = $( '.tooltip', $( this ) ),
				tooltipTimer = 5000;

			if ( $input.prop( 'disabled' ) ) {
				$tooltip.fadeIn();
				setTimeout( function() {
					$tooltip.fadeOut();
				}, tooltipTimer );
			} else {
				$tooltip.hide();
			}
		} );

		$( '#customize-footer-actions' ).on( 'click', '.amp-toggle input', function() {
			api.state( 'ampEnabled' ).set( ! api.state( 'ampEnabled' ).get() );
		} );
	};

	return component;

} )( wp.customize, jQuery );
