/**
 * Adds an 'Enable AMP' toggle to the Gutenberg 'Status & Visibility' section.
 */

/* global wp */
( function() {
	var AMPToggle,
		ComposedAMPToggle,
		el = wp.element.createElement,
		__ = wp.i18n.__,
		PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo,
		FormToggle = wp.components.FormToggle,
		registerPlugin = wp.plugins.registerPlugin;

	/**
	 * The AMP Toggle component
	 *
	 * @param {Object} props The properties, including enabledStatus.
	 * @return {void}
	 */
	AMPToggle = function( props ) {
		return el(
			wp.element.Fragment,
			{},
			el(
				PluginPostStatusInfo,
				{},
				__( 'Enable AMP' ),
				el(
					FormToggle,
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
	};

	/**
	 * The AMP Toggle component, composed with the enabledStatus and a callback for when it's changed.
	 *
	 * @return {void}
	 */
	ComposedAMPToggle = wp.compose.compose(
		wp.data.withSelect( function( select ) {
			return { enabledStatus: select( 'core/editor' ).getEditedPostAttribute( 'meta' ).amp_status };
		} ),
		wp.data.withDispatch( function( dispatch ) {
			return {
				onAmpChange: function( enabledStatus ) {
					let newStatus = ( 'enabled' === enabledStatus ) ? 'disabled' : 'enabled';
					dispatch( 'core/editor' ).editPost( { meta: { amp_status: newStatus } } );
				}
			};
		} ),
		wp.compose.withInstanceId
	)( AMPToggle );

	registerPlugin( 'amp', {
		icon: 'hidden',
		render: ComposedAMPToggle
	} );
} )();
