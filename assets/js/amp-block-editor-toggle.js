/**
 * Adds an 'Enable AMP' toggle to the Gutenberg 'Status & Visibility' section.
 */

/* exported ampBlockEditorToggle */
/* global wp */
var ampBlockEditorToggle = ( function() { // eslint-disable-line no-unused-vars
	'use strict';

	var module = {

		/**
		 * Data from the server.
		 *
		 * @param {Object}
		 */
		data: {
			i18n: {},
			possibleStati: [],
			defaultStatus: ''
		},

		/**
		 * Boots the file.
		 *
		 * @param {Object} data The data for the file, including the default AMP status.
		 * @return {void}
		 */
		boot: function boot( data ) {
			module.data = data;
			wp.i18n.setLocaleData( module.data.i18n, 'amp' );

			wp.plugins.registerPlugin( 'amp', {
				icon: 'hidden',
				render: module.ComposedAMPToggle()
			} );
		},

		/**
		 * The AMP Toggle component
		 *
		 * @param {Object} props The properties, including enabledStatus.
		 * @return {void}
		 */
		AMPToggle: function( props ) {
			var el = wp.element.createElement;
			return el(
				wp.element.Fragment,
				{},
				el(
					wp.editPost.PluginPostStatusInfo,
					{},
					wp.i18n.__( 'Enable AMP' ),
					el(
						wp.components.FormToggle,
						{
							id: 'amp-enabled',
							onChange: function() {
								props.onAmpChange( props.enabledStatus );
							},
							checked: ( 'enabled' === props.enabledStatus )
						}
					)
				)
			);
		},

		/**
		 * The AMP Toggle component, composed with the enabledStatus and a callback for when it's changed.
		 *
		 * @return {void}
		 */
		ComposedAMPToggle: function() {
			return wp.compose.compose(
				wp.data.withSelect( function( select ) {
					var getEnabledStatus = function() {
						var metaSetatus = select( 'core/editor' ).getEditedPostAttribute( 'meta' ).amp_status;
						if ( module.data.possibleStati.includes( metaSetatus ) ) {
							return metaSetatus;
						}
						return module.data.defaultStatus;
					};
					return { enabledStatus: getEnabledStatus() };
				} ),
				wp.data.withDispatch( function( dispatch ) {
					return {
						onAmpChange: function( enabledStatus ) {
							var newStatus = ( 'enabled' === enabledStatus ) ? 'disabled' : 'enabled';
							dispatch( 'core/editor' ).editPost( { meta: { amp_status: newStatus } } );
						}
					};
				} ),
				wp.compose.withInstanceId
			)( module.AMPToggle );
		}
	};

	return module;
} )();
