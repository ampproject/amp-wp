( function( api, $ ) {
	'use strict';

	var ampVars = window.ampVars;

	api.state.add( 'ampEnabled', new api.Value() ).set( false );
	api.state.add( 'ampAvailable', new api.Value() ).set( false );

	/**
	 * Check if the URL is AMPified.
	 *
	 * @param {string} url URL.
	 * @return {boolean} whether it is an AMP URL.
	 */
	function isAmpUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexParam = new RegExp( '(^|\\?|&)' + ampVars.query + '=1(?=&|$)' ),
			regexEndpoint = new RegExp( '\\/' + ampVars.query + '\\/?$' );

		urlParser.href = url;
		if ( regexParam.test( urlParser.search ) ) {
			return true;
		}
		return regexEndpoint.test( urlParser.pathname );
	}

	/**
	 * Create an non-AMP version of a URL.
	 *
	 * @param {string} url URL.
	 * @return {string} non-AMPified URL.
	 */
	function unampifyUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexParam = new RegExp( '(^|\\?|&)' + ampVars.query + '=1' ),
			regexEndpoint = new RegExp( '\\/' + ampVars.query + '\\/?$' );

		urlParser.href = url;
		urlParser.pathname = urlParser.pathname.replace( regexEndpoint, '' );
		urlParser.search = urlParser.search.replace( regexParam, '' );
		return urlParser.href;
	}

	/**
	 * Create an AMP version of a URL.
	 *
	 * @param {string} url URL.
	 * @return {string} AMPified URL.
	 */
	function ampifyUrl( url ) {
		var urlParser = document.createElement( 'a' );
		urlParser.href = unampifyUrl( url );
		if ( urlParser.search.length ) {
			urlParser.search += '&';
		}
		urlParser.search += ampVars.query + '=1';
		return urlParser.href;
	}

	/**
	 * Hook up all AMP preview interactions once panel is ready.
	 *
	 * @param {wp.customize.Panel} panel The AMP panel.
	 * @return {void}
	 */
	function panelReady( panel ) {
		/**
		 * Make current URL AMPified if toggle is on.
		 *
		 * @param {string} url URL.
		 * @return {string} AMPified URL.
		 */
		function setCurrentAmpUrl( url ) {
			var ampToggle = api.state( 'ampEnabled' ).get() && api.state( 'ampAvailable' ).get();
			if ( ! ampToggle && isAmpUrl( url ) ) {
				return unampifyUrl( url );
			} else if ( ampToggle && ! isAmpUrl( url ) ) {
				return ampifyUrl( url );
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
			api.state( 'ampEnabled' ).set( panel.expanded.get() );
		} );

		// Open AMP panel if mobile device selected.
		api.previewedDevice.bind( function( device ) {
			panel.expanded.set( 'mobile' === device );
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
		var template = wp.template( 'amp-customizer-elements' );
		$( '.devices-wrapper' ).before( template( {
			compat: ampVars.strings.compat,
			url: ampVars.post,
			navigate: ampVars.strings.navigate
		} ) );

		// User clicked link within tooltip, go to linked post in preview.
		$( '.amp-toggle .tooltip a' ).on( 'click', function() {
			var url = $( this ).data( 'post' );
			if ( url.length ) {
				api.previewer.previewUrl.set( url );
			}
		} );

		// Main control for toggling AMP preview.
		$( '#customize-footer-actions' ).on( 'click', '.amp-toggle', function() {
			var $input = $( 'input', $( this ) );
			var $tooltip = $( '.tooltip', $( this ) );
			var tooltipTimer = 5000;

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

		// Initial load.
		$( '.amp-toggle input' ).prop( 'disabled', ampVars.ampAvailable );
	}

	api.bind( 'ready', function() {
		api.panel( 'amp_panel', panelReady );
	} );

} )( wp.customize, jQuery );
