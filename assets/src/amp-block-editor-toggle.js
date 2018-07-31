/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { FormToggle, Notice } = wp.components;
const { Fragment } = wp.element;
const { withSelect, withDispatch } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { compose, withInstanceId } = wp.compose;

/**
 * Exported via wp_localize_script().
 */
const { possibleStati, defaultStatus, errorMessages } = window.wpAmpEditor;

/**
 * Adds an 'Enable AMP' toggle to the block editor 'Status & Visibility' section.
 *
 * If there are error(s) that block AMP from being enabled or disabled,
 * this only display a Notice with the error(s), not a toggle.
 * Error(s) are imported as errorMessages via wp_localize_script().
 *
 * @return {Object} AMPToggle component.
 */
function AMPToggle( { enabledStatus, onAmpChange } ) {
	return (
		<Fragment>
			<PluginPostStatusInfo>
				{ ! errorMessages.length && __( 'Enable AMP', 'amp' ) }
				{
					! errorMessages.length &&
					(
						<FormToggle
							checked={ 'enabled' === enabledStatus }
							onChange={ () => onAmpChange( enabledStatus ) }
							id={ 'amp-enabled' }
						/>
					)
				}
				{
					!! errorMessages.length &&
					(
						<Notice
							status={ 'warning' }
							isDismissible={ false }
						>
							{
								errorMessages.map( function( message ) {
									let minSplitLength = 2;

									if ( 'string' === typeof message ) {
										// The message is only a string, so return it.
										return message;
									}
									if ( message[ 0 ].split( '%s' ).length > minSplitLength ) {
										/**
										 * The message is an array with the text in the 0 index, and the href in the 1 index.
										 * And the text should have two %s as placeholders for <a>, like 'AMP cannot be enabled because this %spost type does not support it%s.'.
										 * So split it along %s, to construct the message with the <a>, like:
										 * 'AMP cannot be enabled because this <a href="foo">post type does not support it</a>.'.
										 */
										let splitMessage = message[ 0 ].split( '%s' );
										return ( <p>{ splitMessage[ 0 ] }<a href={ message[ 1 ] }>{ splitMessage[ 1 ] }</a>{ splitMessage[ 2 ] }</p> );
									}
								} )
							}
						</Notice>
					)
				}
			</PluginPostStatusInfo>
		</Fragment>
	);
}

/**
 * The AMP Toggle component, composed with the enabledStatus and a callback for when it's changed.
 *
 * @return {Object} The composed AMP toggle.
 */
function ComposedAMPToggle() {
	return compose( [
		withSelect( ( select ) => {
			/**
			 * Gets the AMP enabled status.
			 *
			 * Uses select from the enclosing function to get the meta value.
			 * If it doesn't exist, it uses the default value.
			 * This applies especially for a new post, where there probably won't be a meta value yet.
			 *
			 * @return {string} Enabled status, either 'enabled' or 'disabled'.
			 */
			let getEnabledStatus = function() {
				let metaSetatus = select( 'core/editor' ).getEditedPostAttribute( 'meta' ).amp_status;
				if ( possibleStati.includes( metaSetatus ) ) {
					return metaSetatus;
				}
				return defaultStatus;
			};

			return { enabledStatus: getEnabledStatus() };
		} ),
		withDispatch( ( dispatch ) => ( {
			onAmpChange: function( enabledStatus ) {
				let newStatus = 'enabled' === enabledStatus ? 'disabled' : 'enabled';
				dispatch( 'core/editor' ).editPost( { meta: { amp_status: newStatus } } );
			}
		} ) ),
		withInstanceId
	] )( AMPToggle );
}

export default wp.plugins.registerPlugin( 'amp', {
	icon: 'hidden',
	render: ComposedAMPToggle()
} );
