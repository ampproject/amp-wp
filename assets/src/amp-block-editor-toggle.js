/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { FormToggle } = wp.components;
const { Fragment } = wp.element;
const { withSelect, withDispatch } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { compose, withInstanceId } = wp.compose;

// @todo: export these values
const possibleStati = [ 'enabled', 'disabled' ];
const defaultStatus = 'enabled';

/**
 * Adds an 'Enable AMP' toggle to the block editor 'Status & Visibility' section.
 *
 * @return {Object} AMPToggle component.
 */
function AMPToggle( { enabledStatus, onAmpChange } ) {
	return (
		<Fragment>
			<PluginPostStatusInfo>
				{ __( 'Enable AMP', 'amp' ) }
				<FormToggle
					checked={ 'enabled' === enabledStatus }
					onChange={ () => onAmpChange( enabledStatus ) }
					id={ 'amp-enabled' }
				/>
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
			 * Uses the select object from the enclosing function to get the meta value.
			 * If it doesn't exist, uses the default value.
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
