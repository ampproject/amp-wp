/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * To render error source information on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Error data.
 * @return {JSX.Element|null} HTML markup for error source data.
 */
export function ErrorSources( { data: errorSources } ) {
	if ( 'object' !== typeof errorSources ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Error Sources', 'amp' ) }
				{ ` (${ errorSources.length || 0 })` }
			</summary>
			<div className="detail-body">
				<p>
					<i>
						<small>
							{ __( 'Please check "Raw Data" for all error source information.', 'amp' ) }
						</small>
					</i>
				</p>
			</div>
		</details>
	);
}

ErrorSources.propTypes = {
	data: PropTypes.object,
};

