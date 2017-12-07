( function( api, $ ) {
	'use strict';

	var ampVars = window.ampVars;

	/**
	 * Check if the URL is AMPified.
	 *
	 * @param {string} url URL.
	 * @return {boolean} whether it is an AMP URL.
	 */
	function isAmpUrl( url ) {
		var urlParser = document.createElement( 'a' ),
			regexParam = new RegExp( "(^|\\?|&)" + ampVars.query + "=1(?=&|$)" ),
			regexEndpoint = new RegExp( "\\/" + ampVars.query + "\\/?$" );

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
			regexParam = new RegExp( "(^|\\?|&)" + ampVars.query + "=1" ),
			regexEndpoint = new RegExp( "\\/" + ampVars.query + "\\/?$" );

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
		var ampToggle = false;

		/**
		 * Make current URL AMPified if toggle is on.
		 *
		 * @param {string} url URL.
		 * @return {string} AMPified URL.
		 */
		function setCurrentAmpUrl( url ) {
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
		function toggleAmpView() {
			api.previewer.previewUrl.set( setCurrentAmpUrl( api.previewer.previewUrl.get() ) );
		}

		/**
		 * Inspect iFrame metadata to see if loaded page was successfully AMP'd.
		 *
		 * @return {void}
		 */
		function isPageAmpified() {
			var ampEnabled = false,
				headContent = $( api.previewer.preview.iframe ).contents().find( 'head' ).html();

			if ( headContent.search( '<meta name="generator" content="AMP Plugin' ) >= 0 ) {
				ampEnabled = true;
			}

			// Update tooltip display based on above IF it's currently toggled on.
			if ( ampToggle && ! ampEnabled ) {
				toggleControl( false, true, true );
			} else if ( ! ampToggle && ! ampEnabled ) {

				// Enable toggle but don't trigger anything else.
				$( '.amp-toggle input' ).prop( 'disabled', false );
			}
		}

		/**
		 * Central function to control UI toggle elements.
		 *
		 * @param {bool} checked If toggle input should be checked.
		 * @param {bool} disabled If toggle input should be disabled.
		 * @param {bool} notification If tooltip notification should appear.
		 * @return {void}
		 */
		function toggleControl( checked, disabled, notification ) {
			var $input = $( '.amp-toggle input' ),
				$tooltip = $( '.amp-toggle .tooltip' ),
				tooltipTimer = 5000;

			$input.prop( 'checked', checked );
			$input.prop( 'disabled', disabled );

			if ( notification ) {
				$tooltip.fadeIn();
				setTimeout( function() {
					$tooltip.fadeOut();
				}, tooltipTimer );
			} else {
				$tooltip.hide();
			}

			$input.trigger( 'change' );
		}

		// AMP panel triggers the input toggle for AMP preview.
		panel.expanded.bind( function() {
			var panelOpen = panel.expanded.get();
			toggleControl( panelOpen, panelOpen, false );
		} );

		// If AMP panel is open, let it handle enable/disable of the toggle,
		// otherwise auto enable toggle when mobile device is selected.
		api.previewedDevice.bind( function( device ) {
			if ( ! panel.expanded.get() && ! ampToggle ) {
				toggleControl( 'mobile' === device, false, false );
			}
		} );

		// Preview is ready, check if it's AMP'd in case we need to show tooltip.
		api.previewer.bind( 'ready', isPageAmpified );

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

		// Adding checkbox toggle before device selection.
		$( '.devices-wrapper' ).before( '<label class="amp-toggle">' +
			'<input type="checkbox">' +
			'<span class="slider"></span>' +
		'</label>' );

		// Notification tooltip for AMP toggle.
		$( '.amp-toggle input' ).before( '<span class="tooltip">' +
			'This page is not AMP compatible.<br>' +
			'<a data-post="' + ampVars.post + '">Navigate to an AMP compatible page</a>' +
		'</span>' );

		$( '.amp-toggle .tooltip a' ).on( 'click', function() {
			var url = $( this ).data( 'post' );
			if ( url.length ) {
				api.previewer.previewUrl.set( setCurrentAmpUrl( url ) );
				toggleControl( false, false, false );
			}
		} );

		// Main control for toggling AMP preview.
		$( '#customize-footer-actions' ).on( 'change', '.amp-toggle input', function( event ) {
			ampToggle = $( this ).is( ':checked' );
			toggleAmpView();
			event.stopPropagation();
		} );
	}

	api.bind( 'ready', function() {
		api.panel( 'amp_panel', panelReady );
	} );

} )( wp.customize, jQuery );
