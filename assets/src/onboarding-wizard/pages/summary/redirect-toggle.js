/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../../../components/options-context-provider';

export function RedirectToggle() {
	const { editedOptions, updateOptions } = useContext( Options );

	const { mobile_redirect: mobileRedirect } = editedOptions;

	return (
		<div className="selectable selectable--bottom">
			<div className="redirect-toggle">
				<ToggleControl
					checked={ true === mobileRedirect }
					label={ (
						<div className="redirect-toggle__label-text">
							<h3>
								{ __( 'Redirect mobile visitors to AMP.', 'amp' ) }
							</h3>
							<p>
								{ __( 'AMP is not only for mobile.', 'amp' ) }
							</p>
						</div>
					) }
					onChange={ () => {
						updateOptions( { mobile_redirect: ! mobileRedirect } );
					} }
				/>
			</div>
		</div>
	);
}
