/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * To render error information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Error data.
 * @return {JSX.Element|null} HTML markup for error data.
 */
export function Errors( { data: errors } ) {
	if ( 'object' !== typeof errors ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Errors', 'amp' ) }
				{ ` (${ errors.length || 0 })` }
			</summary>
			<div className="detail-body">
				<p>
					<i>
						<small>
							{ __( 'Please check "Raw Data" for all error information.', 'amp' ) }
						</small>
					</i>
				</p>
			</div>
		</details>
	);
}

Errors.propTypes = {
	data: PropTypes.array.isRequired,
};

