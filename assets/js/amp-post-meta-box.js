/**
 * This file is part of the AMP Plugin for WordPress.
 *
 * The AMP Plugin for WordPress is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2
 * of the License, or (at your option) any later version.
 *
 * The AMP Plugin for WordPress is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the AMP Plugin for WordPress. If not, see <https://www.gnu.org/licenses/>.
 */

/* exported ampPostMetaBox */

/**
 * AMP Post Meta Box.
 *
 * @todo Rename this to be just the ampEditPostScreen?
 *
 * @since 0.6
 */
var ampPostMetaBox = ( function( $ ) { // eslint-disable-line no-unused-vars
	'use strict';

	var component = {

		/**
		 * Holds data.
		 *
		 * @since 0.6
		 */
		data: {
			canonical: false, // Overridden by amp_is_canonical().
			previewLink: '',
			enabled: true, // Overridden by post_supports_amp( $post ).
			canSupport: true, // Overridden by count( AMP_Post_Type_Support::get_support_errors( $post ) ) === 0.
			statusInputName: '',
			l10n: {
				ampPreviewBtnLabel: ''
			}
		},

		/**
		 * Toggle animation speed.
		 *
		 * @since 0.6
		 */
		toggleSpeed: 200,

		/**
		 * Core preview button selector.
		 *
		 * @since 0.6
		 */
		previewBtnSelector: '#post-preview',

		/**
		 * AMP preview button selector.
		 *
		 * @since 0.6
		 */
		ampPreviewBtnSelector: '#amp-post-preview'
	};

	/**
	 * Boot plugin.
	 *
	 * @since 0.6
	 * @param {Object} data Object data.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		component.data = data;
		$( document ).ready( function() {
			component.statusRadioInputs = $( '[name="' + component.data.statusInputName + '"]' );
			if ( component.data.enabled && ! component.data.canonical ) {
				component.addPreviewButton();
			}
			component.listen();
		} );
	};

	/**
	 * Events listener.
	 *
	 * @since 0.6
	 * @return {void}
	 */
	component.listen = function listen() {
		$( component.ampPreviewBtnSelector ).on( 'click.amp-post-preview', function( e ) {
			e.preventDefault();
			component.onAmpPreviewButtonClick();
		} );

		component.statusRadioInputs.prop( 'disabled', true ); // Prevent cementing setting default status as overridden status.
		$( '.edit-amp-status, [href="#amp_status"]' ).click( function( e ) {
			e.preventDefault();
			component.statusRadioInputs.prop( 'disabled', false );
			component.toggleAmpStatus( $( e.target ) );
		} );

		$( '#submitpost input[type="submit"]' ).on( 'click', function() {
			$( component.ampPreviewBtnSelector ).addClass( 'disabled' );
		} );
	};

	/**
	 * Add AMP Preview button.
	 *
	 * @since 0.6
	 * @return {void}
	 */
	component.addPreviewButton = function addPreviewButton() {
		var previewBtn = $( component.previewBtnSelector );
		previewBtn
			.clone()
			.insertAfter( previewBtn )
			.prop( {
				href: component.data.previewLink,
				id: component.ampPreviewBtnSelector.replace( '#', '' )
			} )
			.text( component.data.l10n.ampPreviewBtnLabel )
			.parent()
			.addClass( 'has-amp-preview' );
	};

	/**
	 * AMP Preview button click handler.
	 *
	 * We trigger the Core preview link for events propagation purposes.
	 *
	 * @since 0.6
	 * @return {void}
	 */
	component.onAmpPreviewButtonClick = function onAmpPreviewButtonClick() {
		var $input;

		// Flag the AMP preview referer.
		$input = $( '<input>' )
			.prop( {
				type: 'hidden',
				name: 'amp-preview',
				value: 'do-preview'
			} )
			.insertAfter( component.ampPreviewBtnSelector );

		// Trigger Core preview button and remove AMP flag.
		$( component.previewBtnSelector ).click();
		$input.remove();
	};

	/**
	 * Add AMP status toggle.
	 *
	 * @since 0.6
	 * @param {Object} $target Event target.
	 * @return {void}
	 */
	component.toggleAmpStatus = function toggleAmpStatus( $target ) {
		var $container = $( '#amp-status-select' ),
			status = $container.data( 'amp-status' ),
			$checked,
			editAmpStatus = $( '.edit-amp-status' );

		// Don't modify status on cancel button click.
		if ( ! $target.hasClass( 'button-cancel' ) ) {
			status = component.statusRadioInputs.filter( ':checked' ).val();
		}

		$checked = $( '#amp-status-' + status );

		// Toggle elements.
		editAmpStatus.fadeToggle( component.toggleSpeed, function() {
			if ( editAmpStatus.is( ':visible' ) ) {
				editAmpStatus.focus();
			} else {
				$container.find( 'input[type="radio"]' ).first().focus();
			}
		} );
		$container.slideToggle( component.toggleSpeed );

		// Update status.
		if ( component.data.canSupport ) {
			$container.data( 'amp-status', status );
			$checked.prop( 'checked', true );
			$( '.amp-status-text' ).text( $checked.next().text() );
		}
	};

	return component;
}( window.jQuery ) );
