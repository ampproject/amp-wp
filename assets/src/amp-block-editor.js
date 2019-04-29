/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { FormToggle, Notice } from '@wordpress/components';
import { Fragment, RawHTML } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { addFilter } from '@wordpress/hooks';
import { compose, withInstanceId } from '@wordpress/compose';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { PrePublishPanel, withCroppedFeaturedImage, withFeaturedImageNotice } from './components';
import { getMinimumFeaturedImageDimensions } from './helpers';

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
				{ ! errorMessages.length && <label htmlFor="amp-enabled">{ __( 'Enable AMP', 'amp' ) }</label> }
				{
					! errorMessages.length &&
					(
						<FormToggle
							checked={ 'enabled' === enabledStatus }
							onChange={ () => onAmpChange( enabledStatus ) }
							id="amp-enabled"
						/>
					)
				}
				{
					!! errorMessages.length &&
					(
						<Notice
							status="warning"
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
			},
		} ) ),
		withInstanceId,
	] )( AMPToggle );
}

// Display a notice in the Featured Image panel if none exists or its width is too small.
addFilter(
	'editor.PostFeaturedImage',
	'ampEditorBlocks/withFeaturedImageNotice',
	withFeaturedImageNotice
);

addFilter( 'editor.MediaUpload', 'ampEditorBlocks/addCroppedFeaturedImage', withCroppedFeaturedImage );

// On clicking 'Publish,' display a notice if no featured image exists or its width is too small.
registerPlugin(
	'amp-post-featured-image-pre-publish',
	{
		render: () => {
			return (
				<PrePublishPanel
					dimensions={ getMinimumFeaturedImageDimensions() }
					required={ false }
				/>
			);
		},
	}
);

export default registerPlugin( 'amp', {
	icon: 'hidden',
	render: ComposedAMPToggle(),
} );
