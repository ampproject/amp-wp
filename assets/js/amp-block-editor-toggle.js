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
		 * @param {Object} {
		 *     The module data.
		 *
		 *     @type {Object} i18n          The internationalization strings.
		 *     @type {Array}  possibleStati The possible enabled stati, including 'enabled'.
		 *     @type {String} defaultStatus The default enabled status for this post.
		 * }
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
		 * The AMP Toggle component.
		 *
		 * @param {Object} props The properties, including enabledStatus.
		 * @return {void}
		 */
		AMPToggle: function AMPToggle( props ) {
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
								props.onAmpChange( props.enabledStatus ); // Use onAMPChange() from ComposedAMPToggle.
							},
							checked: ( 'enabled' === props.enabledStatus ) // Use enabledStatus from ComposedAMPToggle.
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
		ComposedAMPToggle: function ComposedAMPToggle() {
			return wp.compose.compose(
				wp.data.withSelect( function( select ) {

					/**
					 * Gets the AMP enabled status.
					 *
					 * Uses the select object from the enclosing function to get the meta value.
					 * If it doesn't exist, uses the default value.
					 * This applies especially for a new post, where there probably won't be a meta value yet.
					 *
					 * @return {string} Enabled status, either 'enabled' or 'disabled'.
					 */
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
						/**
						 * Toggles the status.
						 * If it was previously 'enabled', it changes to 'disabled'.
						 *
						 * @param {string} enabledStatus The AMP enabled status.
						 */
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
