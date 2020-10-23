/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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

export function RedirectToggle( { direction = 'bottom' } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const { mobile_redirect: mobileRedirect } = editedOptions;

	return (
		<div className={ `selectable selectable--${ direction }` }>
			<AMPSettingToggle
				checked={ true === mobileRedirect }
				title={ __( 'Redirect mobile visitors to AMP', 'amp' ) }
				onChange={ () => {
					updateOptions( { mobile_redirect: ! mobileRedirect } );
				} }
			/>
		</div>
	);
}
RedirectToggle.propTypes = {
	direction: PropTypes.oneOf( [ 'bottom', 'left' ] ),
};
