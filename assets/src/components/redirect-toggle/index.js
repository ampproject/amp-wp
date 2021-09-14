/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../options-context-provider';
import { AMPSettingToggle } from '../amp-setting-toggle';

export function RedirectToggle() {
	const { editedOptions, updateOptions } = useContext( Options );

	const { mobile_redirect: mobileRedirect } = editedOptions;

	return (
		<AMPSettingToggle
			checked={ true === mobileRedirect }
			title={ __( 'Redirect mobile visitors to AMP', 'amp' ) }
			onChange={ () => {
				updateOptions( { mobile_redirect: ! mobileRedirect } );
			} }
		/>
	);
}
