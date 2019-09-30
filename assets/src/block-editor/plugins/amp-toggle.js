
/**
 * External dependencies
 */
import { errorMessages } from 'amp-block-editor-data';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { FormToggle, Notice } from '@wordpress/components';
import { RawHTML, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { withInstanceId } from '@wordpress/compose';

/**
 * Adds an 'Enable AMP' toggle to the block editor 'Status & Visibility' section.
 *
 * If there are error(s) that block AMP from being enabled or disabled,
 * this only displays a Notice with the error(s), not a toggle.
 * Error(s) are imported as errorMessages via wp_localize_script().
 *
 * @return {Object} AMPToggle component.
 */
const AMPToggle = () => {
	const isAMPEnabled = useSelect( ( select ) => {
		const { getDefaultStatus, getPossibleStatuses } = select( 'amp/block-editor' );
		const { getEditedPostAttribute } = select( 'core/editor' );

		const type = getEditedPostAttribute( 'type' );

		if ( 'amp_story' === type ) {
			return true;
		}

		const meta = getEditedPostAttribute( 'meta' );

		if ( meta && meta.amp_status && getPossibleStatuses().includes( meta.amp_status ) ) {
			return 'enabled' === meta.amp_status;
		}

		return 'enabled' === getDefaultStatus();
	} );

	const { editPost } = useDispatch( 'core/editor' );

	const toggleStatus = useCallback( () => {
		const newStatus = isAMPEnabled ? 'disabled' : 'enabled';
		editPost( { meta: { amp_status: newStatus } } );
	}, [ isAMPEnabled, editPost ] );

	return (
		<PluginPostStatusInfo>
			{ ! errorMessages.length && (
				<>
					<label htmlFor="amp-enabled">{ __( 'Enable AMP', 'amp' ) }</label>
					<FormToggle
						checked={ isAMPEnabled }
						onChange={ toggleStatus }
						id="amp-enabled"
					/>
				</>
			) }
			{
				Boolean( errorMessages.length ) &&
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
	);
};

export const name = 'amp';

export const icon = 'hidden';

export const render = withInstanceId( AMPToggle );
