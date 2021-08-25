/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * To render raw data on site support page.
 *
 * @param {Object} props      Component props.
 * @param {Object} props.data Error data.
 * @return {JSX.Element|null} HTML markup for raw data.
 */
export function RawData( { data } ) {
	return (
		<details open={ false }>
			<summary>
				{ __( 'Raw Data', 'amp' ) }
			</summary>
			<pre className="amp-support__raw-data detail-body">
				{ JSON.stringify( data, null, 4 ) }
			</pre>
		</details>
	);
}

RawData.propTypes = {
	data: PropTypes.object.isRequired,
};

