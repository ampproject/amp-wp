
/**
 * External dependencies
 */
import { errorMessages } from 'amp-block-editor-data';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { FormToggle, Notice } from '@wordpress/components';
import { RawHTML } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { compose, withInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { isAMPEnabled } from '../helpers';

/**
 * Adds an 'Enable AMP' toggle to the block editor 'Status & Visibility' section.
 *
 * If there are error(s) that block AMP from being enabled or disabled,
 * this only displays a Notice with the error(s), not a toggle.
 * Error(s) are imported as errorMessages via wp_localize_script().
 *
 * @param {Object} props Component props.
 * @param {boolean} props.isEnabled Whether toggle is enabled.
 * @param {Function} props.onChange Callback function for when the toggle is changed.
 * @return {Object} AMPToggle component.
 */
function AMPToggle( { isEnabled, onChange } ) {
	return (
		<PluginPostStatusInfo>
			{ ! errorMessages.length && (
				<>
					<label htmlFor="amp-enabled">
						{ __( 'Enable AMP', 'amp' ) }
					</label>
					<FormToggle
						checked={ isEnabled }
						onChange={ () => onChange( ! isEnabled ) }
						id="amp-enabled"
					/>
				</>
			) }
			{
				Boolean( errorMessages.length ) &&
					(
						<Notice
							status="info"
							isDismissible={ false }
							className="amp-unavailable-notice"
						>
							<details>
								<summary>
									{ __( 'AMP Unavailable', 'amp' ) }
								</summary>
								{
									errorMessages.map( ( message, index ) => (
										<RawHTML key={ index }>
											{ message }
										</RawHTML>
									) )
								}
							</details>
						</Notice>
					)
			}
		</PluginPostStatusInfo>
	);
}

AMPToggle.propTypes = {
	isEnabled: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
};

export const name = 'amp';

export const icon = 'hidden';

export const render = compose(
	withSelect( () => {
		return {
			isEnabled: isAMPEnabled(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			onChange: ( isEnabled ) => {
				dispatch( 'core/editor' ).editPost( { amp_enabled: isEnabled } );
			},
		};
	} ),
	withInstanceId,
)( AMPToggle );
