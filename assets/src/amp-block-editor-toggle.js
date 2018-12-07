/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { FormToggle, Notice } = wp.components;
const { Fragment, RawHTML } = wp.element;
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
 * this only displays a Notice with the error(s), not a toggle.
 * Error(s) are imported as errorMessages via wp_localize_script().
 *
 * @return {Object} AMPToggle component.
 */
function AMPToggle( { enabledStatus, onAmpChange } ) {
	return (
		<Fragment>
			<PluginPostStatusInfo>
				{ ! errorMessages.length && <label htmlFor='amp-enabled'>{ __( 'Enable AMP', 'amp' ) }</label> }
				{
					! errorMessages.length &&
					(
						<FormToggle
							checked={ 'enabled' === enabledStatus }
							onChange={ () => onAmpChange( enabledStatus ) }
							id='amp-enabled'
						/>
					)
				}
				{
					!! errorMessages.length &&
					(
						<Notice
							status='warning'
							isDismissible={ false }
						>
							{
								errorMessages.map(
									( message, index ) => <RawHTML key={ index }>{ message }</RawHTML>
								)
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
			const getEnabledStatus = () => {
				const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
				if ( meta && meta.amp_status && possibleStati.includes( meta.amp_status ) ) {
					return meta.amp_status;
				}
				return defaultStatus;
			};

			return { enabledStatus: getEnabledStatus() };
		} ),
		withDispatch( ( dispatch ) => ( {
			onAmpChange: ( enabledStatus ) => {
				const newStatus = 'enabled' === enabledStatus ? 'disabled' : 'enabled';
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
